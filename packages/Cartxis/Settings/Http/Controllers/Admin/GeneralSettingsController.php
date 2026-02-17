<?php

namespace Cartxis\Settings\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Cartxis\Core\Services\SettingService;

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
                'store_country' => (string) $this->settingService->get('store_country', ''),
                'site_logo' => (string) $this->settingService->get('site_logo', ''),
                'admin_logo' => (string) $this->settingService->get('admin_logo', ''),
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
            'store_country' => 'required|string|max:100',

            // SEO Settings
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'google_analytics_id' => 'nullable|string|max:50',
            'google_tag_manager_id' => 'nullable|string|max:50',
            'facebook_pixel_id' => 'nullable|string|max:50',
        ]);

        if ($request->hasFile('site_logo')) {
            $logoFile = $request->file('site_logo');

            if (is_array($logoFile)) {
                $request->validate([
                    'site_logo.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120|dimensions:max_width=600,max_height=200',
                ]);
                $logoFile = $logoFile[0] ?? null;
            } else {
                $request->validate([
                    'site_logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120|dimensions:max_width=600,max_height=200',
                ]);
            }

            if ($logoFile) {
                $validated['site_logo'] = $logoFile->store('settings', 'public');
            }
        } elseif ($request->filled('site_logo')) {
            $request->validate([
                'site_logo' => 'string|max:500',
            ]);
            $validated['site_logo'] = $request->input('site_logo');
        }

        if ($request->hasFile('admin_logo')) {
            $adminLogoFile = $request->file('admin_logo');

            if (is_array($adminLogoFile)) {
                $request->validate([
                    'admin_logo.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120|dimensions:max_width=400,max_height=120',
                ]);
                $adminLogoFile = $adminLogoFile[0] ?? null;
            } else {
                $request->validate([
                    'admin_logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120|dimensions:max_width=400,max_height=120',
                ]);
            }

            if ($adminLogoFile) {
                $validated['admin_logo'] = $adminLogoFile->store('settings', 'public');
            }
        } elseif ($request->filled('admin_logo')) {
            $request->validate([
                'admin_logo' => 'string|max:500',
            ]);
            $validated['admin_logo'] = $request->input('admin_logo');
        }

        if ($request->hasFile('site_favicon')) {
            $faviconFile = $request->file('site_favicon');

            if (is_array($faviconFile)) {
                $request->validate([
                    'site_favicon.*' => 'image|mimes:jpeg,png,jpg,gif,svg,ico|max:2048|dimensions:max_width=128,max_height=128',
                ]);
                $faviconFile = $faviconFile[0] ?? null;
            } else {
                $request->validate([
                    'site_favicon' => 'image|mimes:jpeg,png,jpg,gif,svg,ico|max:2048|dimensions:max_width=128,max_height=128',
                ]);
            }

            if ($faviconFile) {
                $validated['site_favicon'] = $faviconFile->store('settings', 'public');
            }
        } elseif ($request->filled('site_favicon')) {
            $request->validate([
                'site_favicon' => 'string|max:500',
            ]);
            $validated['site_favicon'] = $request->input('site_favicon');
        }

        // Save all settings
        foreach ($validated as $key => $value) {
            $this->settingService->set($key, $value, 'string', 'general');
        }

        return back()->with('success', 'General settings saved successfully.');
    }
}
