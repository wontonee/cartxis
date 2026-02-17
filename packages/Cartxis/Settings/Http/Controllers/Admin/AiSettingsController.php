<?php

namespace Cartxis\Settings\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Cartxis\Core\Services\SettingService;

class AiSettingsController
{
    private const PRODUCT_DESCRIPTION_AGENT = 'Product Description Agent';
    private const PRICE_COMPARISON_AGENT = 'Price Comparison Agent';

    public function __construct(
        protected SettingService $settingService
    ) {}

    public function index(): Response
    {
        $agents = $this->normalizeAgents(
            $this->settingService->get('ai.agents', []),
            $this->settingService->get('ai.agents', [])
        );

        return Inertia::render('Admin/Settings/AI/Index', [
            'settings' => [
                'ai_enabled' => (bool) $this->settingService->get('ai.enabled', false),
                'default_provider' => (string) $this->settingService->get('ai.default_provider', ''),
                'default_agent' => (string) $this->settingService->get('ai.default_agent', ''),
                'product_description_agent' => (string) $this->settingService->get('ai.product_description_agent', ''),
                'price_comparison_agent' => (string) $this->settingService->get('ai.price_comparison_agent', ''),
                'providers' => $this->settingService->get('ai.providers', []),
                'models' => $this->settingService->get('ai.models', []),
                'agents' => $agents,
            ],
        ]);
    }

    public function save(Request $request)
    {
        try {
            $rules = [];

            if ($request->has('ai_enabled')) {
                $rules['ai_enabled'] = 'nullable|boolean';
            }
            if ($request->has('default_provider')) {
                $rules['default_provider'] = 'nullable|string|max:100';
            }
            if ($request->has('default_agent')) {
                $rules['default_agent'] = 'nullable|string|max:100';
            }
            if ($request->has('product_description_agent')) {
                $rules['product_description_agent'] = 'nullable|string|max:100';
            }
            if ($request->has('price_comparison_agent')) {
                $rules['price_comparison_agent'] = 'nullable|string|max:100';
            }
            if ($request->has('providers')) {
                $rules['providers'] = 'nullable|array';
                $rules['providers.*.name'] = 'required|string|max:100';
                $rules['providers.*.type'] = 'required|string|max:50';
                $rules['providers.*.base_url'] = 'nullable|string|max:255';
                $rules['providers.*.api_key'] = 'nullable|string|max:255';
                $rules['providers.*.org_id'] = 'nullable|string|max:255';
                $rules['providers.*.project_id'] = 'nullable|string|max:255';
                $rules['providers.*.headers'] = 'nullable|array';
                $rules['providers.*.is_default'] = 'nullable|boolean';
            }
            if ($request->has('models')) {
                $rules['models'] = 'nullable|array';
                $rules['models.*.name'] = 'required|string|max:120';
                $rules['models.*.provider'] = 'required|string|max:100';
                $rules['models.*.mode'] = 'nullable|string|max:50';
                $rules['models.*.max_tokens'] = 'nullable|integer|min:1|max:200000';
                $rules['models.*.is_default'] = 'nullable|boolean';
            }
            if ($request->has('agents')) {
                $rules['agents'] = 'nullable|array';
                $rules['agents.*.name'] = 'required|string|max:120';
                $rules['agents.*.provider'] = 'required|string|max:100';
                $rules['agents.*.model'] = 'required|string|max:120';
                $rules['agents.*.temperature'] = 'nullable|numeric|min:0|max:2';
                $rules['agents.*.max_tokens'] = 'nullable|integer|min:1|max:200000';
                $rules['agents.*.system_prompt'] = 'nullable|string|max:4000';
                $rules['agents.*.is_default'] = 'nullable|boolean';
                $rules['agents.*.is_system'] = 'nullable|boolean';
            }

            $validated = $request->validate($rules);

            if ($request->has('ai_enabled')) {
                $this->settingService->set('ai.enabled', (bool) ($validated['ai_enabled'] ?? false), 'boolean', 'ai');
            }
            if ($request->has('default_provider')) {
                $this->settingService->set('ai.default_provider', $validated['default_provider'] ?? '', 'string', 'ai');
            }
            if ($request->has('default_agent')) {
                $this->settingService->set('ai.default_agent', $validated['default_agent'] ?? '', 'string', 'ai');
            }
            if ($request->has('product_description_agent')) {
                $this->settingService->set('ai.product_description_agent', $validated['product_description_agent'] ?? '', 'string', 'ai');
            }
            if ($request->has('price_comparison_agent')) {
                $this->settingService->set('ai.price_comparison_agent', $validated['price_comparison_agent'] ?? '', 'string', 'ai');
            }
            if ($request->has('providers')) {
                $this->settingService->set('ai.providers', $validated['providers'] ?? [], 'json', 'ai');
            }
            if ($request->has('models')) {
                $this->settingService->set('ai.models', $validated['models'] ?? [], 'json', 'ai');
            }
            if ($request->has('agents')) {
                $existingAgents = $this->settingService->get('ai.agents', []);
                $incomingAgents = $validated['agents'] ?? [];

                $normalizedAgents = $this->normalizeAgents($incomingAgents, $existingAgents);

                $this->settingService->set('ai.agents', $normalizedAgents, 'json', 'ai');
            }

            return redirect()->route('admin.settings.ai.index')
                ->with('success', 'AI settings saved successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('AI settings save error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to save AI settings. Please try again.');
        }
    }

    private function normalizeAgents(array $incomingAgents, mixed $existingAgentsRaw): array
    {
        $systemNames = [self::PRODUCT_DESCRIPTION_AGENT, self::PRICE_COMPARISON_AGENT];
        $existingAgents = is_array($existingAgentsRaw) ? $existingAgentsRaw : [];

        $existingByName = collect($existingAgents)
            ->filter(fn ($agent) => is_array($agent) && !empty($agent['name']))
            ->mapWithKeys(fn ($agent) => [(string) $agent['name'] => $agent]);

        $incoming = collect($incomingAgents)
            ->filter(fn ($agent) => is_array($agent) && !empty($agent['name']))
            ->map(function (array $agent) use ($systemNames) {
                $name = (string) ($agent['name'] ?? '');
                $agent['is_system'] = in_array($name, $systemNames, true);

                return $agent;
            })
            ->values();

        foreach ($systemNames as $systemName) {
            if ($incoming->contains(fn (array $agent) => ($agent['name'] ?? '') === $systemName)) {
                continue;
            }

            $existing = $existingByName->get($systemName);

            if (is_array($existing)) {
                $existing['is_system'] = true;
                $incoming->push($existing);
                continue;
            }

            $incoming->push($this->defaultSystemAgent($systemName));
        }

        return $incoming->values()->all();
    }

    private function defaultSystemAgent(string $name): array
    {
        if ($name === self::PRODUCT_DESCRIPTION_AGENT) {
            return [
                'name' => self::PRODUCT_DESCRIPTION_AGENT,
                'provider' => '',
                'model' => '',
                'temperature' => 0.7,
                'max_tokens' => 1024,
                'system_prompt' => 'You are an AI product description generator for an e-commerce catalog. Use only the provided product data. Never invent facts or specs. If data is missing, omit it. Write in the requested tone and language. Output JSON with: short_description (50-100 words), long_description (200-500 words), meta_description (150-160 chars), bullet_points (5-8), keywords (array), tone_variations (object, optional), confidence_score (0-1), generation_timestamp (ISO 8601). Ensure SEO-friendly phrasing, clear benefits, and consistent brand voice. Avoid banned or unsafe claims.',
                'is_default' => false,
                'is_system' => true,
            ];
        }

        return [
            'name' => self::PRICE_COMPARISON_AGENT,
            'provider' => '',
            'model' => '',
            'temperature' => 0.6,
            'max_tokens' => 800,
            'system_prompt' => 'You are an ecommerce pricing analyst. Based on product name, category, and store country, produce exactly 4 pricing options as JSON only. Each option must include variation, source_label, price, and details. Do not output markdown.',
            'is_default' => false,
            'is_system' => true,
        ];
    }
}
