<?php

declare(strict_types=1);

namespace Tests\Feature\I18n;

use Cartxis\Core\Database\Seeders\LocaleSeeder;
use Cartxis\Core\Models\Locale;
use Cartxis\Core\Services\LocaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(LocaleSeeder::class);
});

test('locale seeder creates middle east europe and africa languages', function () {
    expect(Locale::query()->where('code', 'en')->where('is_default', true)->exists())->toBeTrue()
        ->and(Locale::query()->where('code', 'ar')->where('direction', 'rtl')->exists())->toBeTrue()
        ->and(Locale::query()->whereIn('code', ['fr', 'de', 'es', 'sw', 'am'])->count())->toBe(5);
});

test('locale seeder preserves merchant default when re-run', function () {
    Locale::query()->where('code', 'tr')->update(['is_default' => true]);
    Locale::query()->where('code', '!=', 'tr')->update(['is_default' => false]);

    $this->seed(LocaleSeeder::class);

    expect(Locale::query()->where('code', 'tr')->where('is_default', true)->exists())->toBeTrue()
        ->and(Locale::query()->where('is_default', true)->count())->toBe(1);
});

test('locale switch sets session cookie and app locale', function () {
    $response = $this->from(url('/'))->get('/locale/ar?redirect='.urlencode(url('/')));

    $response->assertRedirect(url('/'));
    $response->assertPlainCookie(LocaleService::COOKIE_KEY, 'ar');
    expect(session(LocaleService::SESSION_KEY))->toBe('ar');
});

test('set locale middleware applies arabic rtl direction on subsequent request', function () {
    $this->withCookie(LocaleService::COOKIE_KEY, 'ar')
        ->withSession([LocaleService::SESSION_KEY => 'ar'])
        ->get('/');

    expect(app()->getLocale())->toBe('ar');
});

test('locale service loads arabic ui messages with english fallback keys', function () {
    $messages = app(LocaleService::class)->uiMessages('ar');

    expect($messages['common']['cart'] ?? null)->toBe('السلة')
        ->and($messages['common']['home'] ?? null)->not->toBeEmpty();
});

test('api locales endpoint returns seeded locales', function () {
    $response = $this->getJson('/api/v1/locales');

    $response->assertOk()
        ->assertJsonPath('success', true);

    $codes = collect($response->json('data.locales'))->pluck('code');
    expect($codes)->toContain('en', 'ar', 'fr', 'sw');
});

test('api accepts x-locale header', function () {
    $response = $this->withHeader('X-Locale', 'ar')
        ->getJson('/api/v1/locales');

    $response->assertOk()
        ->assertJsonPath('data.current', 'ar')
        ->assertJsonPath('data.direction', 'rtl');
});

test('store default locale wins over browser Accept-Language', function () {
    Locale::query()->where('code', 'tr')->update(['is_default' => true]);
    Locale::query()->where('code', '!=', 'tr')->update(['is_default' => false]);
    app(LocaleService::class)->clearCache();

    $this->withHeader('Accept-Language', 'en-US,en;q=0.9')
        ->get('/');

    expect(app()->getLocale())->toBe('tr');
});

test('spa locale switch returns json i18n payload without redirect', function () {
    $response = $this->getJson('/locale/tr?spa=1');

    $response->assertOk()
        ->assertJsonPath('locale', 'tr')
        ->assertJsonPath('i18n.locale', 'tr')
        ->assertJsonPath('i18n.messages.common.login', 'Giriş yap');

    expect(session(LocaleService::SESSION_KEY))->toBe('tr');
});

test('ui messages merge discovered extension locale catalogs with english fallback', function () {
    $code = 'cartxis-i18n-fixture';
    $path = base_path("extension/{$code}");

    File::ensureDirectoryExists("{$path}/resources/locales");
    File::put("{$path}/extension.json", json_encode([
        'code' => $code,
        'name' => 'I18n Fixture',
        'version' => '1.0.0',
    ], JSON_THROW_ON_ERROR));
    File::put("{$path}/resources/locales/en.json", json_encode([
        'extensions' => [
            'cartxis_i18n_fixture' => [
                'hello' => 'Hello from extension',
                'only_en' => 'English only',
            ],
        ],
    ], JSON_THROW_ON_ERROR));
    File::put("{$path}/resources/locales/tr.json", json_encode([
        'extensions' => [
            'cartxis_i18n_fixture' => [
                'hello' => 'Eklentiden merhaba',
            ],
        ],
    ], JSON_THROW_ON_ERROR));

    try {
        $en = app(LocaleService::class)->uiMessages('en');
        expect($en['extensions']['cartxis_i18n_fixture']['hello'] ?? null)->toBe('Hello from extension');

        $tr = app(LocaleService::class)->uiMessages('tr');
        expect($tr['extensions']['cartxis_i18n_fixture']['hello'] ?? null)->toBe('Eklentiden merhaba')
            ->and($tr['extensions']['cartxis_i18n_fixture']['only_en'] ?? null)->toBe('English only');
    } finally {
        File::deleteDirectory($path);
    }
});

test('flat extension locale json is auto-namespaced under extensions snake code', function () {
    $code = 'flat-locale-pack';
    $path = base_path("extension/{$code}");

    File::ensureDirectoryExists("{$path}/resources/locales");
    File::put("{$path}/extension.json", json_encode([
        'code' => $code,
        'name' => 'Flat Pack',
        'version' => '1.0.0',
    ], JSON_THROW_ON_ERROR));
    File::put("{$path}/resources/locales/en.json", json_encode([
        'title' => 'Flat title',
    ], JSON_THROW_ON_ERROR));

    try {
        $messages = app(LocaleService::class)->uiMessages('en');
        expect($messages['extensions']['flat_locale_pack']['title'] ?? null)->toBe('Flat title');
    } finally {
        File::deleteDirectory($path);
    }
});
