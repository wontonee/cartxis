<?php

namespace Cartxis\Product\Services;

use Cartxis\Core\Services\SettingService;
use Cartxis\Product\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiProductDescriptionService
{
    private const PRODUCT_DESCRIPTION_AGENT_KEY = 'ai.product_description_agent';
    private const PRICE_COMPARISON_AGENT_KEY = 'ai.price_comparison_agent';

    public function __construct(
        protected SettingService $settings
    ) {}

    public function generate(Product $product, array $payload = []): array
    {
        $context = $this->resolveExecutionContext(
            $payload,
            self::PRODUCT_DESCRIPTION_AGENT_KEY,
            $this->defaultSystemPrompt(),
            0.7,
            1024
        );

        $input = $this->buildInput($product, $payload);
        $userPrompt = $this->buildUserPrompt($input);

        $content = $this->requestAiContent(
            $context['provider'],
            $context['model_name'],
            $context['system_prompt'],
            $userPrompt,
            $context['temperature'],
            $context['max_tokens']
        );

        $parsed = $this->extractJson($content);

        $parsed['generation_timestamp'] = $parsed['generation_timestamp'] ?? now()->toIso8601String();
        $parsed['confidence_score'] = $parsed['confidence_score'] ?? null;

        return [
            'data' => $parsed,
        ];
    }

    public function generatePriceComparison(array $payload): array
    {
        $context = $this->resolveExecutionContext(
            $payload,
            self::PRICE_COMPARISON_AGENT_KEY,
            'You are an ecommerce pricing analyst. Return JSON only.',
            0.6,
            800
        );

        $storeContext = $this->resolveStoreContext($payload);
        $country = $storeContext['country'];
        $currency = $storeContext['currency'];
        $region = $storeContext['region'];
        $timezone = $storeContext['timezone'];
        $marketplaces = $storeContext['marketplaces'];
        $currentPrice = isset($payload['current_price']) ? (float) $payload['current_price'] : 0.0;
        $cost = isset($payload['cost']) ? (float) $payload['cost'] : 0.0;

        $input = [
            'product_name' => (string) ($payload['product_name'] ?? ''),
            'category' => (string) ($payload['category'] ?? ''),
            'store_country' => $country,
            'currency' => $currency,
            'region' => $region,
            'timezone' => $timezone,
            'recommended_marketplaces' => $marketplaces,
            'current_price' => $currentPrice,
            'cost' => $cost,
            'required_options' => 4,
        ];

        $userPrompt = $this->buildPriceComparisonPrompt($input);

        $meta = [
            'product_name' => $input['product_name'],
            'category' => $input['category'],
            'store_country' => $country,
            'currency' => $currency,
            'region' => $region,
            'timezone' => $timezone,
            'marketplaces' => $marketplaces,
            'current_price' => $currentPrice,
            'cost' => $cost,
            'provider' => $context['provider_name'],
            'model' => $context['model_name'],
            'agent' => $context['agent_name'],
            'generated_at' => now()->toIso8601String(),
        ];

        $content = $this->requestAiContent(
            $context['provider'],
            $context['model_name'],
            $context['system_prompt'],
            $userPrompt,
            $context['temperature'],
            $context['max_tokens']
        );

        $parsed = $this->extractJson($content);
        $rawOptions = $this->extractRawPriceOptions($parsed);
        $options = $this->normalizePriceOptions(
            is_array($rawOptions) ? $rawOptions : [],
            $currentPrice,
            $currency,
            $marketplaces
        );

        if (count($options) === 0) {
            Log::warning('Price comparison AI response parsed with zero valid options.', [
                'provider' => $context['provider_name'],
                'model' => $context['model_name'],
                'agent' => $context['agent_name'],
                'top_level_keys' => array_keys($parsed),
                'raw_options_type' => gettype($rawOptions),
            ]);

            throw new \RuntimeException('AI returned no valid price comparison options.');
        }

        return [
            'options' => $options,
            'meta' => $meta,
        ];
    }

    protected function resolveExecutionContext(
        array $payload,
        string $agentSettingKey,
        string $fallbackSystemPrompt,
        float $defaultTemperature,
        int $defaultMaxTokens
    ): array {
        if (!$this->settings->get('ai.enabled', false)) {
            throw new \RuntimeException('AI is disabled.');
        }

        $providers = $this->settings->get('ai.providers', []);
        $models = $this->settings->get('ai.models', []);
        $agents = $this->settings->get('ai.agents', []);

        $defaultProviderName = (string) $this->settings->get('ai.default_provider', '');
        $defaultAgentName = (string) $this->settings->get('ai.default_agent', '');
        $configuredAgentName = (string) $this->settings->get($agentSettingKey, '');

        $requestedAgentName = isset($payload['agent']) ? trim((string) $payload['agent']) : '';
        $agentName = $requestedAgentName !== ''
            ? $requestedAgentName
            : ($configuredAgentName !== '' ? $configuredAgentName : $defaultAgentName);

        $agent = $this->findByName($agents, $agentName) ?? [];

        $providerName = $this->resolveProviderName($agent, $payload, $defaultProviderName, $providers);
        $provider = $this->findByName($providers, $providerName);
        if (!$provider) {
            throw new \RuntimeException('AI provider not configured.');
        }

        $modelName = $this->resolveModelName($agent, $payload, $models, $providerName);
        if (!$modelName) {
            throw new \RuntimeException('AI model not configured.');
        }

        return [
            'agent_name' => $agentName,
            'agent' => $agent,
            'provider_name' => $providerName,
            'provider' => $provider,
            'model_name' => $modelName,
            'system_prompt' => $agent['system_prompt'] ?? $fallbackSystemPrompt,
            'temperature' => isset($agent['temperature']) ? (float) $agent['temperature'] : $defaultTemperature,
            'max_tokens' => isset($agent['max_tokens']) ? (int) $agent['max_tokens'] : $defaultMaxTokens,
        ];
    }

    protected function requestAiContent(
        array $provider,
        string $modelName,
        string $systemPrompt,
        string $userPrompt,
        float $temperature,
        int $maxTokens
    ): string {
        return match ($provider['type'] ?? 'custom') {
            'gemini' => $this->callGemini($provider, $modelName, $systemPrompt, $userPrompt, $temperature, $maxTokens),
            default => $this->callOpenAiCompatible($provider, $modelName, $systemPrompt, $userPrompt, $temperature, $maxTokens),
        };
    }

    protected function buildInput(Product $product, array $payload): array
    {
        $product->loadMissing(['brand', 'categories', 'attributeValues.attribute']);

        $attributes = $payload['attributes'] ?? null;
        if ($attributes === null && $product->attributeValues->isNotEmpty()) {
            $attributes = $product->attributeValues->mapWithKeys(function ($value) {
                return [$value->attribute?->name ?? $value->attribute?->code ?? $value->attribute_id => $value->value];
            })->toArray();
        }

        $categoryPath = $payload['category'] ?? $this->buildCategoryPath($product);

        return [
            'product_title' => $payload['product_title'] ?? $product->name,
            'category' => $categoryPath,
            'attributes' => $attributes ?? [],
            'images' => $payload['images'] ?? [],
            'brand' => $payload['brand'] ?? $product->brand?->name,
            'key_features' => $payload['key_features'] ?? [],
            'target_audience' => $payload['target_audience'] ?? null,
            'tone_preference' => $payload['tone_preference'] ?? 'professional',
            'language' => $payload['language'] ?? 'en',
        ];
    }

    protected function buildCategoryPath(Product $product): ?string
    {
        if ($product->categories->isEmpty()) {
            return null;
        }

        return $product->categories->pluck('name')->implode(' > ');
    }

    protected function buildUserPrompt(array $input): string
    {
        $schema = [
            'short_description' => '50-100 words',
            'long_description' => '200-500 words',
            'meta_description' => '150-160 characters',
            'bullet_points' => '5-8 bullets',
            'tone_variations' => 'optional object of alternate tones',
            'keywords' => 'array',
            'confidence_score' => '0-1',
            'generation_timestamp' => 'ISO 8601',
        ];

        $payload = [
            'input' => $input,
            'requirements' => [
                'Use only provided data. Do not invent specs.',
                'Return JSON only, no markdown or extra text.',
                'SEO optimized with clear benefits and consistent brand voice.',
            ],
            'schema' => $schema,
        ];

        return "Generate product description JSON.\n" . json_encode($payload, JSON_PRETTY_PRINT);
    }

    protected function defaultSystemPrompt(): string
    {
        return 'You are an AI product description generator for an e-commerce catalog. Use only the provided product data. Never invent facts or specs. If data is missing, omit it. Write in the requested tone and language. Output JSON only.';
    }

    protected function buildPriceComparisonPrompt(array $input): string
    {
        $payload = [
            'input' => $input,
            'requirements' => [
                'Return exactly 4 pricing options.',
                'Use concise source labels from the recommended marketplaces for this store context.',
                'Prices must be positive numeric values in the requested currency.',
                'Respect store country, region and currency context.',
                'Return JSON only with no markdown.',
            ],
            'schema' => [
                'price_options' => [
                    [
                        'variation' => 'undercut|competitive|balanced|premium',
                        'source_label' => 'string',
                        'price' => 'number',
                        'details' => 'short string',
                    ],
                ],
            ],
        ];

        return "Generate price comparison JSON.\n" . json_encode($payload, JSON_PRETTY_PRINT);
    }

    protected function normalizePriceOptions(array $options, float $currentPrice, string $currency, array $marketplaces = []): array
    {
        $options = $this->normalizeRawPriceOptionsArray($options);

        $variations = ['undercut', 'competitive', 'balanced', 'premium'];
        $normalized = [];

        foreach ($options as $index => $option) {
            if (!is_array($option)) {
                continue;
            }

            $price = $this->extractPriceValue($option);
            if ($price <= 0) {
                continue;
            }

            $variation = trim((string) (
                $option['variation']
                ?? $option['type']
                ?? $option['label']
                ?? $option['tier']
                ?? $option['strategy']
                ?? ($variations[$index] ?? 'competitive')
            ));

            $sourceLabel = trim((string) (
                $option['source_label']
                ?? $option['sourceLabel']
                ?? $option['source']
                ?? $option['marketplace']
                ?? $option['platform']
                ?? ($marketplaces[$index] ?? ('Market Option ' . ($index + 1)))
            ));

            $details = trim((string) (
                $option['details']
                ?? $option['detail']
                ?? $option['reason']
                ?? $option['description']
                ?? $option['note']
                ?? 'AI-estimated competitor market signal.'
            ));

            $optionCurrency = strtoupper(trim((string) (
                $option['currency']
                ?? $option['currency_code']
                ?? $option['currencyCode']
                ?? $currency
            )));

            $normalized[] = [
                'variation' => $variation !== '' ? $variation : ($variations[$index] ?? 'competitive'),
                'source_label' => $sourceLabel !== '' ? $sourceLabel : ($marketplaces[$index] ?? ('Market Option ' . ($index + 1))),
                'price' => round($price, 2),
                'currency' => $optionCurrency !== '' ? $optionCurrency : $currency,
                'details' => $details,
            ];

            if (count($normalized) >= 4) {
                break;
            }
        }

        return $normalized;
    }

    protected function normalizeRawPriceOptionsArray(array $options): array
    {
        if (isset($options['items']) && is_array($options['items'])) {
            $options = $options['items'];
        }

        if (isset($options['list']) && is_array($options['list'])) {
            $options = $options['list'];
        }

        $isSequential = array_keys($options) === range(0, count($options) - 1);
        if ($isSequential) {
            return $options;
        }

        $normalized = [];
        foreach ($options as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            if (!isset($value['variation']) && is_string($key)) {
                $value['variation'] = $key;
            }

            $normalized[] = $value;
        }

        return $normalized;
    }

    protected function extractPriceValue(array $option): float
    {
        $candidates = [
            $option['price'] ?? null,
            $option['amount'] ?? null,
            $option['recommended_price'] ?? null,
            $option['recommendedPrice'] ?? null,
            $option['final_price'] ?? null,
            $option['finalPrice'] ?? null,
            $option['price_amount'] ?? null,
            $option['priceAmount'] ?? null,
            $option['value'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_array($candidate)) {
                $nestedCandidates = [
                    $candidate['amount'] ?? null,
                    $candidate['value'] ?? null,
                    $candidate['price'] ?? null,
                ];

                foreach ($nestedCandidates as $nested) {
                    if (is_int($nested) || is_float($nested)) {
                        $nestedValue = (float) $nested;
                        if ($nestedValue > 0) {
                            return $nestedValue;
                        }
                    }

                    if (is_string($nested)) {
                        $raw = trim($nested);
                        if ($raw !== '') {
                            $clean = preg_replace('/[^0-9.,-]/', '', $raw) ?? '';
                            if ($clean !== '') {
                                if (str_contains($clean, ',') && str_contains($clean, '.')) {
                                    $clean = str_replace(',', '', $clean);
                                } elseif (str_contains($clean, ',') && !str_contains($clean, '.')) {
                                    $clean = str_replace(',', '.', $clean);
                                }

                                $nestedValue = (float) $clean;
                                if ($nestedValue > 0) {
                                    return $nestedValue;
                                }
                            }
                        }
                    }
                }

                continue;
            }

            if (is_int($candidate) || is_float($candidate)) {
                $value = (float) $candidate;
                if ($value > 0) {
                    return $value;
                }
                continue;
            }

            if (!is_string($candidate)) {
                continue;
            }

            $raw = trim($candidate);
            if ($raw === '') {
                continue;
            }

            $clean = preg_replace('/[^0-9.,-]/', '', $raw) ?? '';
            if ($clean === '') {
                continue;
            }

            if (str_contains($clean, ',') && str_contains($clean, '.')) {
                $clean = str_replace(',', '', $clean);
            } elseif (str_contains($clean, ',') && !str_contains($clean, '.')) {
                $clean = str_replace(',', '.', $clean);
            }

            $value = (float) $clean;
            if ($value > 0) {
                return $value;
            }
        }

        return 0.0;
    }

    protected function extractRawPriceOptions(array $parsed): array
    {
        $isSequentialTopLevel = array_keys($parsed) === range(0, count($parsed) - 1);
        if ($isSequentialTopLevel) {
            return $parsed;
        }

        $candidates = [
            $parsed['price_options'] ?? null,
            $parsed['priceOptions'] ?? null,
            $parsed['options'] ?? null,
            $parsed['recommendations'] ?? null,
            $parsed['suggestions'] ?? null,
            $parsed['data']['price_options'] ?? null,
            $parsed['data']['priceOptions'] ?? null,
            $parsed['data']['options'] ?? null,
            $parsed['data']['recommendations'] ?? null,
            $parsed['result']['price_options'] ?? null,
            $parsed['result']['priceOptions'] ?? null,
            $parsed['result']['options'] ?? null,
            $parsed['result']['recommendations'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_array($candidate)) {
                return $candidate;
            }

            if (is_string($candidate)) {
                $decoded = json_decode($candidate, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        return [];
    }

    protected function resolveStoreContext(array $payload): array
    {
        $countryCandidate = (string) (
            $payload['store_country']
                ?? $this->settings->get('store_country')
                ?? $this->settings->get('country')
                ?? $this->settings->get('business_country')
                ?? ''
        );

        $currency = strtoupper(trim((string) (
            $payload['currency']
                ?? $this->settings->get('currency')
                ?? config('app.currency', 'USD')
        )));

        if ($currency === '') {
            $currency = 'USD';
        }

        $timezone = trim((string) (
            $payload['timezone']
                ?? $this->settings->get('timezone')
                ?? config('app.timezone', 'UTC')
        ));

        if ($timezone === '') {
            $timezone = 'UTC';
        }

        $country = $this->inferCountry($countryCandidate, $currency, $timezone);
        $region = $this->resolveRegionFromCountry($country) ?: ($this->resolveRegionFromTimezone($timezone) ?? 'Global');
        $marketplaces = $this->resolveMarketplaces($country, $region);

        return [
            'country' => $country,
            'currency' => $currency,
            'timezone' => $timezone,
            'region' => $region,
            'marketplaces' => $marketplaces,
        ];
    }

    protected function inferCountry(string $countryCandidate, string $currency, string $timezone): string
    {
        $normalized = $this->normalizeCountryName($countryCandidate);
        if ($normalized !== '') {
            return $normalized;
        }

        $timezoneLower = strtolower($timezone);
        if (str_contains($timezoneLower, 'kolkata') || str_contains($timezoneLower, 'calcutta')) {
            return 'India';
        }
        if (str_contains($timezoneLower, 'london')) {
            return 'United Kingdom';
        }
        if (str_contains($timezoneLower, 'new_york') || str_contains($timezoneLower, 'chicago') || str_contains($timezoneLower, 'los_angeles')) {
            return 'United States';
        }

        return match ($currency) {
            'INR' => 'India',
            'GBP' => 'United Kingdom',
            'USD' => 'United States',
            'EUR' => 'Germany',
            'AUD' => 'Australia',
            'CAD' => 'Canada',
            default => 'Global',
        };
    }

    protected function normalizeCountryName(string $country): string
    {
        $value = strtolower(trim($country));
        if ($value === '') {
            return '';
        }

        return match ($value) {
            'india', 'in' => 'India',
            'uk', 'united kingdom', 'great britain', 'gb', 'england' => 'United Kingdom',
            'us', 'usa', 'united states', 'united states of america' => 'United States',
            'ca', 'canada' => 'Canada',
            'au', 'australia' => 'Australia',
            'de', 'germany' => 'Germany',
            'fr', 'france' => 'France',
            'ae', 'united arab emirates', 'uae' => 'United Arab Emirates',
            default => ucwords($value),
        };
    }

    protected function resolveRegionFromCountry(string $country): ?string
    {
        return match ($country) {
            'India', 'United Arab Emirates' => 'Asia',
            'United Kingdom', 'Germany', 'France' => 'Europe',
            'United States', 'Canada' => 'North America',
            'Australia' => 'Oceania',
            default => null,
        };
    }

    protected function resolveRegionFromTimezone(string $timezone): ?string
    {
        $tz = strtolower($timezone);

        if (str_contains($tz, 'asia/')) {
            return 'Asia';
        }
        if (str_contains($tz, 'europe/')) {
            return 'Europe';
        }
        if (str_contains($tz, 'america/')) {
            return 'North America';
        }
        if (str_contains($tz, 'australia/')) {
            return 'Oceania';
        }

        return null;
    }

    protected function resolveMarketplaces(string $country, string $region): array
    {
        $byCountry = [
            'India' => ['Flipkart', 'Amazon.in', 'Croma', 'Reliance Digital'],
            'United Kingdom' => ['Amazon.co.uk', 'Argos', 'Currys', 'John Lewis'],
            'United States' => ['Amazon.com', 'Walmart', 'Best Buy', 'Target'],
            'Canada' => ['Amazon.ca', 'Best Buy Canada', 'Walmart Canada', 'Newegg Canada'],
            'Australia' => ['Amazon.com.au', 'JB Hi-Fi', 'Harvey Norman', 'The Good Guys'],
            'Germany' => ['Amazon.de', 'MediaMarkt', 'Saturn', 'Otto'],
            'France' => ['Amazon.fr', 'Cdiscount', 'Fnac', 'Darty'],
            'United Arab Emirates' => ['Amazon.ae', 'Noon', 'Sharaf DG', 'Carrefour UAE'],
        ];

        if (isset($byCountry[$country])) {
            return $byCountry[$country];
        }

        return match ($region) {
            'Asia' => ['Amazon', 'Noon', 'Lazada', 'Shopee'],
            'Europe' => ['Amazon', 'eBay', 'MediaMarkt', 'Cdiscount'],
            'North America' => ['Amazon', 'Walmart', 'Best Buy', 'eBay'],
            'Oceania' => ['Amazon', 'JB Hi-Fi', 'Harvey Norman', 'eBay'],
            default => ['Amazon', 'eBay', 'AliExpress', 'Regional Marketplace'],
        };
    }

    protected function callOpenAiCompatible(array $provider, string $model, string $systemPrompt, string $userPrompt, float $temperature, int $maxTokens): string
    {
        $baseUrl = rtrim((string) ($provider['base_url'] ?? 'https://api.openai.com/v1'), '/');
        $url = Str::contains($baseUrl, '/chat/completions')
            ? $baseUrl
            : $baseUrl . '/chat/completions';

        $headers = array_merge([
            'Authorization' => 'Bearer ' . ($provider['api_key'] ?? ''),
            'Content-Type' => 'application/json',
        ], $provider['headers'] ?? []);

        $response = Http::withHeaders($headers)->post($url, [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('AI provider request failed: ' . $response->body());
        }

        return Arr::get($response->json(), 'choices.0.message.content', '');
    }

    protected function callGemini(array $provider, string $model, string $systemPrompt, string $userPrompt, float $temperature, int $maxTokens): string
    {
        $baseUrl = rtrim((string) ($provider['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta/models'), '/');
        $apiKey = (string) ($provider['api_key'] ?? '');

        $modelName = Str::startsWith($model, 'models/') ? Str::after($model, 'models/') : $model;
        if (Str::contains($baseUrl, ':generateContent')) {
            $url = Str::contains($baseUrl, '{model}')
                ? str_replace('{model}', $modelName, $baseUrl)
                : $baseUrl;
        } else {
            $url = Str::endsWith($baseUrl, '/models')
                ? $baseUrl . '/' . $modelName . ':generateContent'
                : $baseUrl . '/models/' . $modelName . ':generateContent';
        }

        $headers = array_merge([
            'Content-Type' => 'application/json',
        ], $provider['headers'] ?? []);

        $response = Http::withHeaders($headers)
            ->post($url . '?key=' . $apiKey, [
                'system_instruction' => [
                    'parts' => [
                        ['text' => $systemPrompt],
                    ],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $userPrompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => $temperature,
                    'maxOutputTokens' => $maxTokens,
                ],
            ]);

        if (!$response->successful()) {
            Log::error('Gemini request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $url,
            ]);
            throw new \RuntimeException('AI provider request failed: ' . $response->body());
        }

        return Arr::get($response->json(), 'candidates.0.content.parts.0.text', '');
    }

    protected function extractJson(string $content): array
    {
        $content = trim($content);
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        if ($start !== false && $end !== false) {
            $substring = substr($content, $start, $end - $start + 1);
            $decoded = json_decode($substring, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        throw new \RuntimeException('AI response was not valid JSON.');
    }

    protected function findByName(array $items, ?string $name): ?array
    {
        if (!$name) {
            return null;
        }

        $needle = trim((string) $name);
        if ($needle === '') {
            return null;
        }

        foreach ($items as $item) {
            $itemName = trim((string) ($item['name'] ?? ''));
            if ($itemName !== '' && strcasecmp($itemName, $needle) === 0) {
                return $item;
            }
        }

        return null;
    }

    protected function getDefaultModel(array $models, ?string $providerName): ?string
    {
        $providerModels = array_values(array_filter($models, function ($model) use ($providerName) {
            return ($model['provider'] ?? null) === $providerName;
        }));

        foreach ($providerModels as $model) {
            if (!empty($model['is_default'])) {
                return $model['name'] ?? null;
            }
        }

        return $providerModels[0]['name'] ?? null;
    }

    protected function resolveProviderName(array $agent, array $payload, string $defaultProviderName, array $providers): ?string
    {
        $candidates = [
            $agent['provider'] ?? null,
            $payload['provider'] ?? null,
            $defaultProviderName,
        ];

        foreach ($candidates as $candidate) {
            $name = is_string($candidate) ? trim($candidate) : '';
            if ($name !== '' && $this->findByName($providers, $name)) {
                return $name;
            }
        }

        foreach ($providers as $provider) {
            $name = trim((string) ($provider['name'] ?? ''));
            if ($name !== '') {
                return $name;
            }
        }

        return null;
    }

    protected function resolveModelName(array $agent, array $payload, array $models, ?string $providerName): ?string
    {
        $candidates = [
            $agent['model'] ?? null,
            $payload['model'] ?? null,
            $this->getDefaultModel($models, $providerName),
        ];

        foreach ($candidates as $candidate) {
            $name = is_string($candidate) ? trim($candidate) : '';
            if ($name !== '') {
                return $name;
            }
        }

        foreach ($models as $model) {
            if (($model['provider'] ?? null) === $providerName) {
                $name = trim((string) ($model['name'] ?? ''));
                if ($name !== '') {
                    return $name;
                }
            }
        }

        foreach ($models as $model) {
            $name = trim((string) ($model['name'] ?? ''));
            if ($name !== '') {
                return $name;
            }
        }

        return null;
    }
}
