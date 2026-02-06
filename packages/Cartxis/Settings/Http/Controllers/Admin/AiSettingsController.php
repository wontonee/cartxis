<?php

namespace Cartxis\Settings\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Cartxis\Core\Services\SettingService;

class AiSettingsController
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Settings/AI/Index', [
            'settings' => [
                'ai_enabled' => (bool) $this->settingService->get('ai.enabled', false),
                'default_provider' => (string) $this->settingService->get('ai.default_provider', ''),
                'default_agent' => (string) $this->settingService->get('ai.default_agent', ''),
                'product_description_agent' => (string) $this->settingService->get('ai.product_description_agent', ''),
                'providers' => $this->settingService->get('ai.providers', []),
                'models' => $this->settingService->get('ai.models', []),
                'agents' => $this->settingService->get('ai.agents', []),
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
            if ($request->has('providers')) {
                $this->settingService->set('ai.providers', $validated['providers'] ?? [], 'json', 'ai');
            }
            if ($request->has('models')) {
                $this->settingService->set('ai.models', $validated['models'] ?? [], 'json', 'ai');
            }
            if ($request->has('agents')) {
                $this->settingService->set('ai.agents', $validated['agents'] ?? [], 'json', 'ai');
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
}
