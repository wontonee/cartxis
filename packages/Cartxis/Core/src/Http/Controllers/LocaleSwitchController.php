<?php

declare(strict_types=1);

namespace Cartxis\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Cartxis\Core\Services\LocaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class LocaleSwitchController extends Controller
{
    public function __invoke(Request $request, string $locale, LocaleService $locales): Response|JsonResponse|RedirectResponse
    {
        if (! $locales->isActive($locale)) {
            abort(404);
        }

        $request->session()->put(LocaleService::SESSION_KEY, $locale);
        app()->setLocale($locale);

        try {
            $user = $request->user();
            if ($user && \Illuminate\Support\Facades\Schema::hasColumn($user->getTable(), 'preferred_locale')) {
                $user->forceFill(['preferred_locale' => $locale])->save();
            }
        } catch (\Throwable) {
            //
        }

        try {
            if (array_key_exists('customer', config('auth.guards', []))) {
                $customer = $request->user('customer');
                if ($customer && \Illuminate\Support\Facades\Schema::hasColumn($customer->getTable(), 'preferred_locale')) {
                    $customer->forceFill(['preferred_locale' => $locale])->save();
                }
            }
        } catch (\Throwable) {
            //
        }

        Cookie::queue(LocaleService::COOKIE_KEY, $locale, 60 * 24 * 365);

        // SPA switcher: return locale payload so the client can sync vue-i18n
        // without requiring a manual hard refresh.
        if ($request->boolean('spa') || $request->expectsJson()) {
            return response()->json([
                'locale' => $locale,
                'i18n' => [
                    'locale' => $locale,
                    'fallbackLocale' => config('app.fallback_locale', 'en'),
                    'direction' => $locales->directionFor($locale),
                    'intlLocale' => $locales->intlLocale($locale),
                    'availableLocales' => $locales->availableLocales(),
                    'messages' => $locales->uiMessages($locale),
                ],
            ]);
        }

        $redirect = $request->query('redirect', url()->previous() ?: '/');
        $appHost = parse_url((string) url('/'), PHP_URL_HOST);
        $isSafe = false;

        if (is_string($redirect) && $redirect !== '') {
            // Same-origin relative path (preferred from the language switcher).
            if (str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')) {
                $isSafe = true;
            } else {
                $redirectHost = parse_url($redirect, PHP_URL_HOST);
                $isSafe = is_string($redirectHost) && $redirectHost === $appHost;
            }
        }

        if (! $isSafe) {
            $redirect = url('/');
        }

        // Inertia absolute redirects can leave the SPA with stale vue-i18n state.
        // Force a same-document location visit so props + i18n remount cleanly.
        if ($request->header('X-Inertia')) {
            return Inertia::location($redirect);
        }

        return redirect()->to($redirect);
    }
}
