<?php
// database/seeders/SuperAdminSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create super admin user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'admin@ezm.local'],
            [
                'name' => 'Super Administrator',
                'first_name' => 'Super',
                'last_name' => 'Administrator',
                'password' => Hash::make('password123'),
                'user_type' => 'super_admin',
                'is_active' => true,
                'two_factor_enabled' => false, // Disable 2FA for super admin initially
                'two_factor_method' => 'email',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Super admin created: admin@ezm.local / password123');
    }
}

// Add to database/seeders/DatabaseSeeder.php:
// $this->call(SuperAdminSeeder::class);