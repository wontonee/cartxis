<?php

declare(strict_types=1);

namespace Cartxis\Setup\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Cartxis\Setup\Services\DemoDataService;
use Cartxis\Core\Models\Country;
use Cartxis\Core\Services\SettingService;
use Cartxis\Core\Models\Currency;
use App\Models\User;

class SetupController extends Controller
{
    public function __construct(
        private DemoDataService $demoDataService,
        private SettingService $settingService
    ) {}

    /**
     * Show welcome screen
     */
    public function welcome(): Response
    {
        return Inertia::render('Setup/Welcome', [
            'appName' => config('app.name', 'Cartxis'),
            'appVersion' => '1.0.0',
        ]);
    }

    /**
     * Show business type selection
     */
    public function businessType(): Response
    {
        return Inertia::render('Setup/BusinessType', [
            'businessTypes' => $this->demoDataService->getBusinessTypes(),
        ]);
    }

    /**
     * Show business settings screen
     */
    public function businessSettings(Request $request): Response
    {
        $businessType = $request->query('type', 'retail');

        $countries = Country::active()->ordered()->get(['name', 'code'])->toArray();

        $currencies = Country::active()
            ->whereNotNull('currency_code')
            ->selectRaw('currency_code as code, MIN(currency_symbol) as symbol')
            ->groupBy('currency_code')
            ->orderBy('currency_code')
            ->get()
            ->toArray();

        return Inertia::render('Setup/BusinessSettings', [
            'businessType' => $businessType,
            'countries'    => $countries,
            'currencies'   => $currencies,
        ]);
    }

    /**
     * Save business settings
     */
    public function saveBusinessSettings(Request $request)
    {
        $validated = $request->validate([
            'business_type' => 'required|string',
            'store_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'nullable|string|min:8|confirmed',
            'contact_phone' => 'nullable|string|max:50',
            'store_address' => 'nullable|string',
            'store_country' => 'required|string|max:100',
            'currency' => 'required|string|max:10',
            'timezone' => 'required|string|max:100',
        ]);

        try {
            // Save settings for setup wizard (raw in settings table)
            foreach ($validated as $key => $value) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    [
                        'key' => $key,
                        'value' => $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // Also save to general settings using SettingService (for admin panel)
            $this->settingService->set('site_name', $validated['store_name'], 'string', 'general');
            $this->settingService->set('admin_email', $validated['admin_email'], 'string', 'general');
            $this->settingService->set('contact_phone', $validated['contact_phone'] ?? '', 'string', 'general');
            $this->settingService->set('contact_address', $validated['store_address'] ?? '', 'string', 'general');
            $this->settingService->set('store_country', $validated['store_country'], 'string', 'general');

            // Optionally create or update admin user credentials
            if (!empty($validated['admin_password'])) {
                $adminUser = User::where('email', $validated['admin_email'])->first();

                if ($adminUser) {
                    $adminUser->fill([
                        'name' => $adminUser->name ?: 'Admin User',
                        'role' => 'admin',
                        'is_active' => true,
                        'email_verified_at' => $adminUser->email_verified_at ?? now(),
                    ]);
                    $adminUser->password = Hash::make($validated['admin_password']);
                    $adminUser->save();
                } else {
                    User::create([
                        'name' => 'Admin User',
                        'email' => $validated['admin_email'],
                        'password' => Hash::make($validated['admin_password']),
                        'role' => 'admin',
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]);
                }
            }

            $currencyCode = strtoupper($validated['currency']);
            $currencyMeta = $this->getCurrencyMetadata($currencyCode);

            Currency::updateOrCreate(
                ['code' => $currencyCode],
                [
                    'name' => $currencyMeta['name'],
                    'symbol' => $currencyMeta['symbol'],
                    'symbol_position' => $currencyMeta['symbol_position'],
                    'decimal_places' => $currencyMeta['decimal_places'],
                    'exchange_rate' => $currencyMeta['exchange_rate'],
                    'is_default' => true,
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Business settings saved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show demo data import screen
     */
    public function demoData(Request $request): Response
    {
        $businessType = $request->query('type', 'retail');

        return Inertia::render('Setup/DemoData', [
            'businessType' => $businessType,
            'businessTypes' => $this->demoDataService->getBusinessTypes(),
        ]);
    }

    /**
     * Import demo data
     */
    public function importDemoData(Request $request)
    {
        $validated = $request->validate([
            'business_type' => 'required|string|in:retail,kirana,electronics,fashion',
            'import_products' => 'boolean',
        ]);

        try {
            $result = $this->demoDataService->importDemoData(
                $validated['business_type'],
                $validated['import_products'] ?? true
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'stats' => $result['stats'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import demo data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete setup
     */
    public function complete(Request $request)
    {
        try {
            $this->demoDataService->markSetupComplete();

            // Clear all caches after setup completion
            Artisan::call('optimize:clear');

            return response()->json([
                'success' => true,
                'message' => 'Setup completed successfully',
                'redirect' => '/admin/login',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete setup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show completion screen
     */
    public function finish(): Response
    {
        return Inertia::render('Setup/Finish');
    }

    private function getCurrencyMetadata(string $code): array
    {
        $country = Country::whereNotNull('currency_code')
            ->where('currency_code', $code)
            ->first(['currency_symbol']);

        $symbol = $country?->currency_symbol ?? $code;

        return [
            'name'           => $code,
            'symbol'         => $symbol,
            'symbol_position' => 'before',
            'decimal_places' => 2,
            'exchange_rate'  => 1.0,
        ];
    }
}
