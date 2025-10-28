<?php

namespace Vortex\Settings\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Core\Services\SettingService;

class StoreConfigurationController
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function index(): Response
    {
        return Inertia::render('Admin/Settings/StoreConfiguration/Index', [
            'settings' => [
                // Store Details
                'store_name' => (string) $this->settingService->get('store_name', ''),
                'store_description' => (string) $this->settingService->get('store_description', ''),
                'business_registration' => (string) $this->settingService->get('business_registration', ''),
                'vat_number' => (string) $this->settingService->get('vat_number', ''),
                'store_license' => (string) $this->settingService->get('store_license', ''),
                
                // Contact Information
                'store_email' => (string) $this->settingService->get('store_email', ''),
                'support_email' => (string) $this->settingService->get('support_email', ''),
                'store_phone' => (string) $this->settingService->get('store_phone', ''),
                'store_phone_alt' => (string) $this->settingService->get('store_phone_alt', ''),
                'store_whatsapp' => (string) $this->settingService->get('store_whatsapp', ''),
                
                // Address Information
                'store_address_1' => (string) $this->settingService->get('store_address_1', ''),
                'store_address_2' => (string) $this->settingService->get('store_address_2', ''),
                'store_city' => (string) $this->settingService->get('store_city', ''),
                'store_state' => (string) $this->settingService->get('store_state', ''),
                'store_postal_code' => (string) $this->settingService->get('store_postal_code', ''),
                'store_country' => (string) $this->settingService->get('store_country', ''),
                
                // Store Timezone
                'store_timezone' => (string) $this->settingService->get('store_timezone', 'UTC'),
                
                // Social Media
                'social_facebook' => (string) $this->settingService->get('social_facebook', ''),
                'social_instagram' => (string) $this->settingService->get('social_instagram', ''),
                'social_twitter' => (string) $this->settingService->get('social_twitter', ''),
                'social_linkedin' => (string) $this->settingService->get('social_linkedin', ''),
                'social_youtube' => (string) $this->settingService->get('social_youtube', ''),
                'social_tiktok' => (string) $this->settingService->get('social_tiktok', ''),
                'social_pinterest' => (string) $this->settingService->get('social_pinterest', ''),
                
                // Policies
                'policy_privacy' => (string) $this->settingService->get('policy_privacy', ''),
                'policy_terms' => (string) $this->settingService->get('policy_terms', ''),
                'policy_return' => (string) $this->settingService->get('policy_return', ''),
                'policy_shipping' => (string) $this->settingService->get('policy_shipping', ''),
                
                // Checkout Options
                'checkout_allow_guest' => (bool) $this->settingService->get('checkout_allow_guest', true),
                'checkout_require_account' => (bool) $this->settingService->get('checkout_require_account', false),
            ],
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            // Store Details
            'store_name' => 'required|string|max:255',
            'store_description' => 'nullable|string',
            'business_registration' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'store_license' => 'nullable|string|max:255',
            
            // Contact Information
            'store_email' => 'required|email|max:255',
            'support_email' => 'nullable|email|max:255',
            'store_phone' => 'required|string|max:50',
            'store_phone_alt' => 'nullable|string|max:50',
            'store_whatsapp' => 'nullable|string|max:50',
            
            // Address Information
            'store_address_1' => 'required|string|max:255',
            'store_address_2' => 'nullable|string|max:255',
            'store_city' => 'required|string|max:100',
            'store_state' => 'required|string|max:100',
            'store_postal_code' => 'required|string|max:20',
            'store_country' => 'required|string|max:100',
            
            // Store Timezone
            'store_timezone' => 'required|string|max:50',
            
            // Social Media
            'social_facebook' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_twitter' => 'nullable|url|max:255',
            'social_linkedin' => 'nullable|url|max:255',
            'social_youtube' => 'nullable|url|max:255',
            'social_tiktok' => 'nullable|url|max:255',
            'social_pinterest' => 'nullable|url|max:255',
            
            // Policies
            'policy_privacy' => 'nullable|string',
            'policy_terms' => 'nullable|string',
            'policy_return' => 'nullable|string',
            'policy_shipping' => 'nullable|string',
            
            // Checkout Options
            'checkout_allow_guest' => 'nullable|boolean',
            'checkout_require_account' => 'nullable|boolean',
        ]);

        foreach ($validated as $key => $value) {
            $this->settingService->set($key, $value, 'string', 'store');
        }

        return back()->with('success', 'Store configuration saved successfully.');
    }
}
