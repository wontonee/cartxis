<?php

declare(strict_types=1);

namespace Cartxis\Setup\Services;

use Cartxis\CMS\Services\StorefrontMenuSyncService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DemoDataService
{
    /**
     * Available catalog starters for setup.
     *
     * Groups:
     * - physical: shippable retail verticals with demo seeders
     * - digital: downloadable / virtual catalog starter
     * - services: quote / RFQ catalog starter
     * - blank: empty catalog (no demo seeder)
     */
    private const BUSINESS_TYPES = [
        'retail' => [
            'name' => 'Retail Store',
            'description' => 'General retail with clothing, electronics, and accessories',
            'group' => 'physical',
            'group_label' => 'Physical retail',
            'has_demo' => true,
            'seeders' => [
                \Cartxis\Setup\Database\Seeders\RetailDemoSeeder::class,
            ],
        ],
        'kirana' => [
            'name' => 'Kirana / Grocery',
            'description' => 'Grocery and daily essentials for neighborhood stores',
            'group' => 'physical',
            'group_label' => 'Physical retail',
            'has_demo' => true,
            'seeders' => [
                \Cartxis\Setup\Database\Seeders\KiranaDemoSeeder::class,
            ],
        ],
        'electronics' => [
            'name' => 'Electronics Store',
            'description' => 'Consumer electronics, gadgets, and tech accessories',
            'group' => 'physical',
            'group_label' => 'Physical retail',
            'has_demo' => true,
            'seeders' => [
                \Cartxis\Setup\Database\Seeders\ElectronicsDemoSeeder::class,
            ],
        ],
        'fashion' => [
            'name' => 'Fashion & Apparel',
            'description' => 'Clothing, shoes, and fashion accessories',
            'group' => 'physical',
            'group_label' => 'Physical retail',
            'has_demo' => true,
            'seeders' => [
                \Cartxis\Setup\Database\Seeders\FashionDemoSeeder::class,
            ],
        ],
        'digital' => [
            'name' => 'Digital & Downloads',
            'description' => 'Sell ebooks, software, courses, and other downloadable products',
            'group' => 'digital',
            'group_label' => 'Digital products',
            'has_demo' => true,
            'seeders' => [
                \Cartxis\Setup\Database\Seeders\DigitalDemoSeeder::class,
            ],
        ],
        'rfq' => [
            'name' => 'Quote / RFQ',
            'description' => 'B2B and custom pricing — customers request quotes instead of buying online',
            'group' => 'services',
            'group_label' => 'Quotes & services',
            'has_demo' => true,
            'seeders' => [
                \Cartxis\Setup\Database\Seeders\QuoteDemoSeeder::class,
            ],
        ],
        'blank' => [
            'name' => 'Start Blank',
            'description' => 'Empty catalog — add your own products, downloads, or quote items later',
            'group' => 'blank',
            'group_label' => 'Empty catalog',
            'has_demo' => false,
            'seeders' => [],
        ],
    ];

    /**
     * Allowed business type ids (for validation).
     *
     * @return list<string>
     */
    public function allowedTypeIds(): array
    {
        return array_keys(self::BUSINESS_TYPES);
    }

    /**
     * Get all available business types for the setup UI.
     */
    public function getBusinessTypes(): array
    {
        return array_map(function ($key, $data) {
            return [
                'id' => $key,
                'name' => $data['name'],
                'description' => $data['description'],
                'group' => $data['group'],
                'group_label' => $data['group_label'],
                'has_demo' => (bool) $data['has_demo'],
            ];
        }, array_keys(self::BUSINESS_TYPES), self::BUSINESS_TYPES);
    }

    public function hasDemo(string $businessType): bool
    {
        return (bool) (self::BUSINESS_TYPES[$businessType]['has_demo'] ?? false);
    }

    /**
     * Import demo data for the selected business type.
     */
    public function importDemoData(string $businessType, bool $importProducts = true): array
    {
        if (! isset(self::BUSINESS_TYPES[$businessType])) {
            throw new \InvalidArgumentException("Invalid business type: {$businessType}");
        }

        $results = [
            'success' => true,
            'message' => '',
            'stats' => [],
        ];

        try {
            DB::beginTransaction();

            $config = self::BUSINESS_TYPES[$businessType];
            $shouldImport = $importProducts && ! empty($config['seeders']);

            if ($shouldImport) {
                foreach ($config['seeders'] as $seederClass) {
                    Log::info("Running seeder: {$seederClass}");

                    if (! class_exists($seederClass)) {
                        throw new \Exception("Seeder class not found: {$seederClass}. Run 'composer dump-autoload' on the server.");
                    }

                    $exitCode = Artisan::call('db:seed', [
                        '--class' => $seederClass,
                        '--force' => true,
                    ]);

                    $output = Artisan::output();
                    Log::info("Seeder output: {$output}, Exit code: {$exitCode}");

                    if ($exitCode !== 0) {
                        throw new \Exception("Seeder failed with exit code {$exitCode}: {$output}");
                    }
                }

                $results['stats'] = $this->getImportStatistics();
                $results['message'] = "Demo data imported successfully for {$config['name']}";

                $sync = app(StorefrontMenuSyncService::class);
                $sync->fixDealsUrls();
                $synced = $sync->syncCategoryMenuItems();
                Log::info("Storefront category menu synced: {$synced} item(s)");
            } else {
                $results['message'] = $businessType === 'blank'
                    ? 'Blank catalog selected — no sample products imported'
                    : "Catalog type saved as {$config['name']} without sample products";
            }

            DB::table('settings')->updateOrInsert(
                ['key' => 'business_type'],
                [
                    'key' => 'business_type',
                    'value' => $businessType,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Demo data import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $results['success'] = false;
            $results['message'] = 'Failed to import demo data: '.$e->getMessage();
        }

        return $results;
    }

    private function getImportStatistics(): array
    {
        return [
            'categories' => DB::table('categories')->count(),
            'products' => DB::table('products')->count(),
            'brands' => DB::table('brands')->count(),
            'pages' => DB::table('pages')->count(),
            'blocks' => DB::table('blocks')->count(),
        ];
    }

    public function markSetupComplete(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'setup_completed'],
            [
                'key' => 'setup_completed',
                'value' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
