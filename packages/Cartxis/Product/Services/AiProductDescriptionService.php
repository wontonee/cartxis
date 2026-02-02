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
    public function __construct(
        protected SettingService $settings
    ) {}

    public function generate(Product $product, array $payload = []): array
    {
        if (!$this->settings->get('ai.enabled', false)) {
            throw new \RuntimeException('AI is disabled.');
        }

        $providers = $this->settings->get('ai.providers', []);
        $models = $this->settings->get('ai.models', []);
        $agents = $this->settings->get('ai.agents', []);
        $defaultProviderName = (string) $this->settings->get('ai.default_provider', '');
        $defaultAgentName = (string) $this->settings->get('ai.default_agent', '');
        $productDescriptionAgent = (string) $this->settings->get('ai.product_description_agent', '');

        $agentName = $payload['agent'] ?? $productDescriptionAgent ?: $defaultAgentName;
        $agent = $this->findByName($agents, $agentName) ?? [];

        $providerName = $agent['provider'] ?? $payload['provider'] ?? $defaultProviderName;
        if (!$providerName) {
            $providerName = $providers[0]['name'] ?? null;
        }

        $provider = $this->findByName($providers, $providerName);
        if (!$provider) {
            throw new \RuntimeException('AI provider not configured.');
        }

        $modelName = $agent['model'] ?? $payload['model'] ?? $this->getDefaultModel($models, $providerName);
        if (!$modelName) {
            throw new \RuntimeException('AI model not configured.');
        }

        $systemPrompt = $agent['system_prompt'] ?? $this->defaultSystemPrompt();
        $temperature = isset($agent['temperature']) ? (float) $agent['temperature'] : 0.7;
        $maxTokens = isset($agent['max_tokens']) ? (int) $agent['max_tokens'] : 1024;

        $input = $this->buildInput($product, $payload);
        $userPrompt = $this->buildUserPrompt($input);

        $content = match ($provider['type'] ?? 'custom') {
            'gemini' => $this->callGemini($provider, $modelName, $systemPrompt, $userPrompt, $temperature, $maxTokens),
            default => $this->callOpenAiCompatible($provider, $modelName, $systemPrompt, $userPrompt, $temperature, $maxTokens),
        };

        $parsed = $this->extractJson($content);

        $parsed['generation_timestamp'] = $parsed['generation_timestamp'] ?? now()->toIso8601String();
        $parsed['confidence_score'] = $parsed['confidence_score'] ?? null;

        return [
            'data' => $parsed,
        ];
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

        foreach ($items as $item) {
            if (($item['name'] ?? null) === $name) {
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
}
