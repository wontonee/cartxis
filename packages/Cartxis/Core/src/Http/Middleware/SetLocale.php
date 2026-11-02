<?php

declare(strict_types=1);

namespace Cartxis\Core\Http\Middleware;

use Cartxis\Core\Services\LocaleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(
        private LocaleService $locales,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $customerPreferred = null;
        try {
            $user = $request->user();
            if ($user && isset($user->preferred_locale) && is_string($user->preferred_locale)) {
                $customerPreferred = $user->preferred_locale;
            }
        } catch (\Throwable) {
            //
        }

        try {
            if (array_key_exists('customer', config('auth.guards', []))) {
                $customer = $request->user('customer');
                if ($customer && isset($customer->preferred_locale) && is_string($customer->preferred_locale)) {
                    $customerPreferred = $customer->preferred_locale;
                }
            }
        } catch (\Throwable) {
            // Auth guards may not be ready during early bootstrap / install.
        }

        $locale = $this->locales->resolveFromRequest(
            explicit: $request->query('locale') ?? $request->header('X-Locale'),
            session: $request->session()->get(LocaleService::SESSION_KEY),
            cookie: $request->cookie(LocaleService::COOKIE_KEY),
            customerPreferred: $customerPreferred,
            acceptLanguage: $request->header('Accept-Language'),
        );

        app()->setLocale($locale);
        $request->attributes->set('cartxis_locale', $locale);
        $request->attributes->set('cartxis_direction', $this->locales->directionFor($locale));

        /** @var Response $response */
        $response = $next($request);

        return $response;
    }
}
