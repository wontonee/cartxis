<?php

namespace App\Http\Middleware;

use Cartxis\Core\Services\SettingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class HandleAppearance
{
    public function __construct(private SettingService $settingService) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appearance = $request->cookie('appearance') ?? 'system';
        $forceLightStorefront = false;

        if (! $request->is('admin') && ! $request->is('admin/*')) {
            try {
                $theme = \Cartxis\Core\Models\Theme::active();
                $config = $theme?->getConfig() ?? [];

                // Native-homepage themes ship light-only CSS; system/admin dark mode breaks them.
                if (! empty($config['native_homepage'])) {
                    $appearance = 'light';
                    $forceLightStorefront = true;
                }
            } catch (\Throwable $e) {
                // ignore during install/migrations
            }
        }

        View::share('appearance', $appearance);
        View::share('forceLightStorefront', $forceLightStorefront);

        $storedFavicon = $this->settingService->get('site_favicon');
        $faviconUrl = $storedFavicon
            ? Storage::disk('public')->url($storedFavicon)
            : '/logos/favicon.png';

        View::share('favicon', $faviconUrl);

        return $next($request);
    }
}
