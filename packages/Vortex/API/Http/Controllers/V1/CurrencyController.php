<?php

namespace Vortex\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Vortex\API\Helpers\ApiResponse;
use Vortex\Core\Models\Currency;

class CurrencyController extends Controller
{
    /**
     * Get default currency for the application.
     */
    public function default()
    {
        $currency = Currency::getDefault();

        if (!$currency) {
            return ApiResponse::error('Default currency not configured', null, 500, 'CURRENCY_NOT_CONFIGURED');
        }

        return ApiResponse::success([
            'code' => $currency->code,
            'name' => $currency->name,
            'symbol' => $currency->symbol,
            'symbol_position' => $currency->symbol_position,
            'decimal_places' => $currency->decimal_places,
            'decimal_separator' => $currency->decimal_separator,
            'thousands_separator' => $currency->thousands_separator,
            'exchange_rate' => $currency->exchange_rate,
        ], 'Default currency retrieved successfully');
    }

    /**
     * Get all active currencies.
     */
    public function index()
    {
        $currencies = Currency::where('status', 'active')
            ->orderBy('sort_order')
            ->get(['code', 'name', 'symbol', 'symbol_position', 'decimal_places', 'decimal_separator', 'thousands_separator', 'exchange_rate', 'is_default']);

        return ApiResponse::success($currencies, 'Currencies retrieved successfully');
    }
}
