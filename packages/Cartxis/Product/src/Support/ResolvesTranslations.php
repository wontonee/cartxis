<?php

declare(strict_types=1);

namespace Cartxis\Product\Support;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Resolve localized attributes from *_translations with fallback to base columns.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait ResolvesTranslations
{
    abstract public function translations(): HasMany;

    /**
     * @return list<string>
     */
    abstract protected function translatableAttributes(): array;

    public function translate(?string $locale = null): static
    {
        $locale = $locale ?: app()->getLocale();
        $fallback = (string) config('app.fallback_locale', 'en');

        if (! $this->relationLoaded('translations')) {
            $this->load('translations');
        }

        $row = $this->translations->firstWhere('locale', $locale)
            ?? ($locale !== $fallback ? $this->translations->firstWhere('locale', $fallback) : null);

        if (! $row) {
            return $this;
        }

        foreach ($this->translatableAttributes() as $attribute) {
            $value = $row->{$attribute} ?? null;
            if (is_string($value) && $value !== '') {
                $this->setAttribute($attribute, $value);
            }
        }

        return $this;
    }

    public function translationFor(string $locale): ?object
    {
        if (! $this->relationLoaded('translations')) {
            $this->load('translations');
        }

        return $this->translations->firstWhere('locale', $locale);
    }

    /**
     * Upsert translation rows keyed by locale.
     *
     * @param  array<string, array<string, mixed>>  $byLocale
     */
    public function syncTranslations(array $byLocale): void
    {
        foreach ($byLocale as $locale => $attributes) {
            if (! is_string($locale) || $locale === '') {
                continue;
            }

            $payload = [];
            foreach ($this->translatableAttributes() as $attribute) {
                if (array_key_exists($attribute, $attributes)) {
                    $payload[$attribute] = $attributes[$attribute];
                }
            }

            if ($payload === []) {
                continue;
            }

            $this->translations()->updateOrCreate(
                ['locale' => $locale],
                $payload
            );
        }
    }
}
