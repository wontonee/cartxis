<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test customer user
        User::updateOrCreate(
            ['email' => 'customer@test.com'],
            [
                'name' => 'Test Customer',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => 'customer',
                'is_active' => true,
            ]
        );

        // Create test admin user
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Test Admin',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->command->info('Test users created successfully!');
        $this->command->info('Customer: customer@test.com / password123');
        $this->command->info('Admin: admin@test.com / admin123');
    }
}
