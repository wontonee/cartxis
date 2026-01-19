<?php

declare(strict_types=1);

namespace Cartxis\Setup\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DemoDataService
{
    /**
     * Available business types with their demo data seeders
     */
    private const BUSINESS_TYPES = [
        'retail' => [
            'name' => 'Retail Store',
            'description' => 'General retail shopping with clothing, electronics, and accessories',
            'seeders' => [
                    \Cartxis\Setup\Database\Seeders\RetailDemoSeeder::class,
            ],
        ],
        'kirana' => [
            'name' => 'Kirana/Grocery Store',
            'description' => 'Grocery and daily essentials for neighborhood stores',
            'seeders' => [
                    \Cartxis\Setup\Database\Seeders\KiranaDemoSeeder::class,
            ],
        ],
        'electronics' => [
            'name' => 'Electronics Store',
            'description' => 'Consumer electronics, gadgets, and tech accessories',
            'seeders' => [
                    \Cartxis\Setup\Database\Seeders\ElectronicsDemoSeeder::class,
            ],
        ],
        'fashion' => [
            'name' => 'Fashion & Apparel',
            'description' => 'Clothing, shoes, and fashion accessories',
            'seeders' => [
                    \Cartxis\Setup\Database\Seeders\FashionDemoSeeder::class,
            ],
        ],
    ];

    /**
     * Get all available business types
     */
    public function getBusinessTypes(): array
    {
        return array_map(function ($key, $data) {
            return [
                'id' => $key,
                'name' => $data['name'],
                'description' => $data['description'],
            ];
        }, array_keys(self::BUSINESS_TYPES), self::BUSINESS_TYPES);
    }

    /**
     * Import demo data for the selected business type
     */
    public function importDemoData(string $businessType, bool $importProducts = true): array
    {
        if (!isset(self::BUSINESS_TYPES[$businessType])) {
            throw new \InvalidArgumentException("Invalid business type: {$businessType}");
        }

        $results = [
            'success' => true,
            'message' => '',
            'stats' => [],
        ];

        try {
            DB::beginTransaction();

            if ($importProducts) {
                $config = self::BUSINESS_TYPES[$businessType];
                
                // Run the seeders for this business type
                foreach ($config['seeders'] as $seederClass) {
                    Log::info("Running seeder: {$seederClass}");
                    Artisan::call('db:seed', ['--class' => $seederClass]);
                }

                // Get import statistics
                $results['stats'] = $this->getImportStatistics();
                $results['message'] = "Demo data imported successfully for {$config['name']}";
            }

            // Save the selected business type in settings
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
            $results['message'] = 'Failed to import demo data: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Get import statistics
     */
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

    /**
     * Mark setup as complete
     */
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
