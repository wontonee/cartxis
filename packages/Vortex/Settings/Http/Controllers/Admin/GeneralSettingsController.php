<?php

namespace Vortex\Settings\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Core\Services\SettingService;

class GeneralSettingsController extends Controller
{
    protected SettingService $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * Display the general settings form
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Settings/General/Index', [
            'settings' => [
                // Site Information
                'site_name' => (string) $this->settingService->get('site_name', ''),
                'site_tagline' => (string) $this->settingService->get('site_tagline', ''),
                'admin_email' => (string) $this->settingService->get('admin_email', ''),
                'contact_phone' => (string) $this->settingService->get('contact_phone', ''),
                'contact_address' => (string) $this->settingService->get('contact_address', ''),
                'site_logo' => (string) $this->settingService->get('site_logo', ''),
                'site_favicon' => (string) $this->settingService->get('site_favicon', ''),

                // SEO Settings
                'meta_title' => (string) $this->settingService->get('meta_title', ''),
                'meta_description' => (string) $this->settingService->get('meta_description', ''),
                'meta_keywords' => (string) $this->settingService->get('meta_keywords', ''),
                'google_analytics_id' => (string) $this->settingService->get('google_analytics_id', ''),
                'google_tag_manager_id' => (string) $this->settingService->get('google_tag_manager_id', ''),
                'facebook_pixel_id' => (string) $this->settingService->get('facebook_pixel_id', ''),
            ],
        ]);
    }

    /**
     * Save general settings
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            // Site Information
            'site_name' => 'required|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'admin_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_address' => 'nullable|string|max:500',
            'site_logo' => 'nullable|string|max:500',
            'site_favicon' => 'nullable|string|max:500',

            // SEO Settings
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'google_analytics_id' => 'nullable|string|max:50',
            'google_tag_manager_id' => 'nullable|string|max:50',
            'facebook_pixel_id' => 'nullable|string|max:50',
        ]);

        // Save all settings
        foreach ($validated as $key => $value) {
            $this->settingService->set($key, $value, 'string', 'general');
        }

        return back()->with('success', 'General settings saved successfully.');
    }
}
