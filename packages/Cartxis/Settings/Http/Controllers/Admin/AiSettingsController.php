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
            $validated = $request->validate([
                'ai_enabled' => 'nullable|boolean',
                'default_provider' => 'nullable|string|max:100',
                'default_agent' => 'nullable|string|max:100',
                'product_description_agent' => 'nullable|string|max:100',
                'providers' => 'nullable|array',
                'providers.*.name' => 'required|string|max:100',
                'providers.*.type' => 'required|string|max:50',
                'providers.*.base_url' => 'nullable|string|max:255',
                'providers.*.api_key' => 'nullable|string|max:255',
                'providers.*.org_id' => 'nullable|string|max:255',
                'providers.*.project_id' => 'nullable|string|max:255',
                'providers.*.headers' => 'nullable|array',
                'providers.*.is_default' => 'nullable|boolean',
                'models' => 'nullable|array',
                'models.*.name' => 'required|string|max:120',
                'models.*.provider' => 'required|string|max:100',
                'models.*.mode' => 'nullable|string|max:50',
                'models.*.max_tokens' => 'nullable|integer|min:1|max:200000',
                'models.*.is_default' => 'nullable|boolean',
                'agents' => 'nullable|array',
                'agents.*.name' => 'required|string|max:120',
                'agents.*.provider' => 'required|string|max:100',
                'agents.*.model' => 'required|string|max:120',
                'agents.*.temperature' => 'nullable|numeric|min:0|max:2',
                'agents.*.max_tokens' => 'nullable|integer|min:1|max:200000',
                'agents.*.system_prompt' => 'nullable|string|max:4000',
                'agents.*.is_default' => 'nullable|boolean',
            ]);

            $this->settingService->set('ai.enabled', (bool) ($validated['ai_enabled'] ?? false), 'boolean', 'ai');
            $this->settingService->set('ai.default_provider', $validated['default_provider'] ?? '', 'string', 'ai');
            $this->settingService->set('ai.default_agent', $validated['default_agent'] ?? '', 'string', 'ai');
            $this->settingService->set('ai.product_description_agent', $validated['product_description_agent'] ?? '', 'string', 'ai');
            $this->settingService->set('ai.providers', $validated['providers'] ?? [], 'json', 'ai');
            $this->settingService->set('ai.models', $validated['models'] ?? [], 'json', 'ai');
            $this->settingService->set('ai.agents', $validated['agents'] ?? [], 'json', 'ai');

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
