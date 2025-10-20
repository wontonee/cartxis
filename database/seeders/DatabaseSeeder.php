<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            AdminMenuSeeder::class,
            ThemeSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            BrandMenuSeeder::class,
            AttributeSeeder::class,
            ProductSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
