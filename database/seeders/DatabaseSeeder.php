<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\Core\Database\Seeders\DatabaseSeeder as CartxisDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the main Cartxis Core Seeder which orchestrates all package seeders
        $this->call(CartxisDatabaseSeeder::class);
        
        // You can add project-specific seeders here if needed
        // $this->call(TransactionSeeder::class);
    }
}
