<?php

namespace Cartxis\Admin\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name     = env('CARTXIS_ADMIN_NAME');
        $email    = env('CARTXIS_ADMIN_EMAIL');
        $password = env('CARTXIS_ADMIN_PASSWORD');

        if (empty($email) || empty($password)) {
            $this->command->error(
                'Admin credentials are not set. Run `php artisan cartxis:install` to set up Cartxis properly.'
            );
            return;
        }

        $name = $name ?: 'Admin';

        User::firstOrCreate(
            ['email' => $email],
            [
                'name'               => $name,
                'password'           => Hash::make($password),
                'role'               => 'admin',
                'is_active'          => true,
                'email_verified_at'  => now(),
            ]
        );

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: ' . $email);
    }
}
