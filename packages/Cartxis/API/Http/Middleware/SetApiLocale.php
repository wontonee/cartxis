<?php

declare(strict_types=1);

namespace Cartxis\API\Http\Middleware;

use Cartxis\Core\Services\LocaleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
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

        $locale = $this->locales->resolveFromRequest(
            explicit: $request->query('locale') ?? $request->header('X-Locale'),
            session: null,
            cookie: $request->cookie(LocaleService::COOKIE_KEY),
            customerPreferred: $customerPreferred,
            acceptLanguage: $request->header('Accept-Language'),
        );

        app()->setLocale($locale);
        $request->attributes->set('cartxis_locale', $locale);
        $request->attributes->set('cartxis_direction', $this->locales->directionFor($locale));

        return $next($request);
    }
}
