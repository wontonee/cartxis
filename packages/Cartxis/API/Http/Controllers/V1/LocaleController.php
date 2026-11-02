<?php

declare(strict_types=1);

namespace Cartxis\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Cartxis\API\Helpers\ApiResponse;
use Cartxis\Core\Services\LocaleService;

class LocaleController extends Controller
{
    public function __construct(
        private LocaleService $locales,
    ) {}

    /**
     * List active storefront locales.
     */
    public function index()
    {
        return ApiResponse::success([
            'current' => app()->getLocale(),
            'fallback' => config('app.fallback_locale', 'en'),
            'direction' => $this->locales->directionFor(app()->getLocale()),
            'locales' => $this->locales->availableLocales(),
        ], 'Locales retrieved successfully');
    }

    /**
     * Get default locale metadata.
     */
    public function default()
    {
        $code = $this->locales->defaultLocaleCode();

        return ApiResponse::success([
            'code' => $code,
            'direction' => $this->locales->directionFor($code),
            'intl_locale' => $this->locales->intlLocale($code),
        ], 'Default locale retrieved successfully');
    }
}
