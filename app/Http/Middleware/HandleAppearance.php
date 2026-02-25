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
        View::share('appearance', $request->cookie('appearance') ?? 'system');

        $storedFavicon = $this->settingService->get('site_favicon');
        $faviconUrl = $storedFavicon
            ? Storage::disk('public')->url($storedFavicon)
            : '/logos/favicon.png';

        View::share('favicon', $faviconUrl);

        return $next($request);
    }
}
