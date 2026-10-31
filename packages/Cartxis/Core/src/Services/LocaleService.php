<?php

declare(strict_types=1);

namespace Cartxis\Core\Services;

use Cartxis\Core\Models\Locale;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class LocaleService
{
    public const SESSION_KEY = 'cartxis_locale';

    public const COOKIE_KEY = 'cartxis_locale';

    /**
     * @return list<array{code: string, name: string, native_name: string|null, direction: string, is_default: bool}>
     */
    public function availableLocales(): array
    {
        try {
            if (! Schema::hasTable('locales')) {
                return [$this->fallbackLocalePayload()];
            }

            return Cache::remember('cartxis.locales.active', 300, function () {
                $locales = Locale::query()
                    ->active()
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get(['code', 'name', 'native_name', 'direction', 'is_default']);

                if ($locales->isEmpty()) {
                    return [$this->fallbackLocalePayload()];
                }

                return $locales->map(fn (Locale $locale) => [
                    'code' => $locale->code,
                    'name' => $locale->name,
                    'native_name' => $locale->native_name,
                    'direction' => $locale->direction,
                    'is_default' => (bool) $locale->is_default,
                ])->values()->all();
            });
        } catch (\Throwable) {
            return [$this->fallbackLocalePayload()];
        }
    }

    public function defaultLocaleCode(): string
    {
        try {
            if (! Schema::hasTable('locales')) {
                return (string) config('app.locale', 'en');
            }

            $default = Locale::getDefault();

            return $default?->code ?: (string) config('app.locale', 'en');
        } catch (\Throwable) {
            return (string) config('app.locale', 'en');
        }
    }

    public function directionFor(string $code): string
    {
        foreach ($this->availableLocales() as $locale) {
            if ($locale['code'] === $code) {
                return $locale['direction'] ?? 'ltr';
            }
        }

        return in_array($code, ['ar', 'he', 'fa', 'ur'], true) ? 'rtl' : 'ltr';
    }

    public function isActive(string $code): bool
    {
        foreach ($this->availableLocales() as $locale) {
            if ($locale['code'] === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve the best locale from request signals.
     */
    public function resolveFromRequest(
        ?string $explicit = null,
        ?string $session = null,
        ?string $cookie = null,
        ?string $customerPreferred = null,
        ?string $acceptLanguage = null,
    ): string {
        // Explicit switcher / API header / session / cookie / customer preference win.
        // Store default (Settings → Locales) comes next — so setting Turkish as
        // default actually drives the storefront for new visitors.
        // Accept-Language is last-resort only when no default locale exists.
        foreach ([$explicit, $session, $cookie, $customerPreferred] as $candidate) {
            if (is_string($candidate) && $candidate !== '' && $this->isActive($candidate)) {
                return $candidate;
            }
        }

        $default = $this->defaultLocaleCode();
        if ($default !== '' && $this->isActive($default)) {
            return $default;
        }

        if (is_string($acceptLanguage) && $acceptLanguage !== '') {
            $parsed = $this->parseAcceptLanguage($acceptLanguage);
            foreach ($parsed as $code) {
                if ($this->isActive($code)) {
                    return $code;
                }
            }
        }

        return $default !== '' ? $default : 'en';
    }

    /**
     * Frontend UI message catalog for vue-i18n.
     *
     * @return array<string, mixed>
     */
    public function uiMessages(string $locale): array
    {
        $fallback = $this->loadJsonCatalog('en');
        $messages = $locale === 'en' ? $fallback : array_replace_recursive($fallback, $this->loadJsonCatalog($locale));

        return $messages;
    }

    /**
     * @return array<string, mixed>
     */
    public function loadJsonCatalog(string $locale): array
    {
        $path = resource_path("js/locales/{$locale}.json");

        if (! File::exists($path)) {
            return [];
        }

        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

            return $decoded;
        } catch (\Throwable) {
            return [];
        }
    }

    public function clearCache(): void
    {
        Cache::forget('cartxis.locales.active');
    }

    /**
     * Map ISO 639-1 to a BCP 47 tag for Intl.
     */
    public function intlLocale(string $code): string
    {
        return match ($code) {
            'en' => 'en-US',
            'ar' => 'ar-SA',
            'he' => 'he-IL',
            'fa' => 'fa-IR',
            'tr' => 'tr-TR',
            'fr' => 'fr-FR',
            'de' => 'de-DE',
            'es' => 'es-ES',
            'it' => 'it-IT',
            'pt' => 'pt-PT',
            'nl' => 'nl-NL',
            'pl' => 'pl-PL',
            'ru' => 'ru-RU',
            'uk' => 'uk-UA',
            'sw' => 'sw-KE',
            'am' => 'am-ET',
            default => $code,
        };
    }

    /**
     * @return list<string>
     */
    private function parseAcceptLanguage(string $header): array
    {
        $parts = array_map('trim', explode(',', $header));
        $codes = [];

        foreach ($parts as $part) {
            $lang = strtolower(explode(';', $part)[0] ?? '');
            $lang = str_replace('_', '-', $lang);
            if ($lang === '') {
                continue;
            }
            $primary = explode('-', $lang)[0];
            $codes[] = $primary;
        }

        return array_values(array_unique($codes));
    }

    /**
     * @return array{code: string, name: string, native_name: string, direction: string, is_default: bool}
     */
    private function fallbackLocalePayload(): array
    {
        $code = (string) config('app.locale', 'en');

        return [
            'code' => $code,
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'is_default' => true,
        ];
    }
}
