<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Vortex\Core\Database\Seeders\DatabaseSeeder as VortexDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the main Vortex Core Seeder which orchestrates all package seeders
        $this->call(VortexDatabaseSeeder::class);
        
        // You can add project-specific seeders here if needed
        // $this->call(TransactionSeeder::class);
    }
}
