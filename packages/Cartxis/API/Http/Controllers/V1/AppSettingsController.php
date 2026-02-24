<?php

namespace Cartxis\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Cartxis\Core\Services\SettingService;

/**
 * AppSettingsController
 *
 * Provides public app configuration for the mobile application,
 * including logos, branding assets and store info.
 *
 * GET /api/v1/app/settings
 */
class AppSettingsController extends Controller
{
    protected SettingService $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * Return app-level settings needed by the mobile app on boot.
     *
     * This endpoint is public (no auth required) so the app can
     * load branding/config before the user logs in (e.g. auth screen logo).
     */
    public function index(): JsonResponse
    {
        $settings = [
            // Branding
            'site_name'        => (string) $this->settingService->get('site_name', ''),
            'site_tagline'     => (string) $this->settingService->get('site_tagline', ''),

            // Logos — return full public URLs so the app can download directly
            'site_logo'        => $this->assetUrl($this->settingService->get('site_logo')),
            'mobile_auth_logo' => $this->assetUrl($this->settingService->get('mobile_auth_logo')),

            // Store info
            'store_country'    => (string) $this->settingService->get('store_country', ''),
            'contact_phone'    => (string) $this->settingService->get('contact_phone', ''),
            'admin_email'      => (string) $this->settingService->get('admin_email', ''),

            // SEO / meta
            'meta_title'       => (string) $this->settingService->get('meta_title', ''),
            'meta_description' => (string) $this->settingService->get('meta_description', ''),

            // API version info
            'api_version'      => 'v1',
        ];

        return response()->json([
            'success' => true,
            'data'    => $settings,
        ]);
    }

    /**
     * Convert a storage-relative path to a full public URL.
     * Returns null if the path is empty.
     */
    protected function assetUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // If it's already a full URL, return as-is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Generate a full URL from the public storage disk
        return Storage::disk('public')->url($path);
    }
}
