<?php
// database/seeders/TestUsersSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Community;
use App\Models\Apartment;
use App\Models\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test community if it doesn't exist
        $community = Community::firstOrCreate(
            ['name' => 'Test Community'],
            [
                'full_name' => 'Wspólnota Mieszkaniowa Test',
                'address_street' => 'ul. Testowa 1',
                'address_postal_code' => '00-001',
                'address_city' => 'Warszawa',
                'address_state' => 'mazowieckie',
                'regon' => '123456789',
                'tax_id' => '1234567890',
                'manager_name' => 'Test Manager Sp. z o.o.',
                'manager_address_street' => 'ul. Zarządcza 5',
                'manager_address_postal_code' => '00-002',
                'manager_address_city' => 'Warszawa',
                'common_area_size' => 100.00,
                'apartments_area' => 1000.00,
                'short_full_name' => 'WM Test',
                'is_active' => true,
                'has_elevator' => false,
                'residential_water_meters' => 20,
                'main_water_meters' => 1,
                'apartment_count' => 20,
                'staircase_count' => 1,
                'color' => '#3b82f6',
            ]
        );

        // Create test apartments
        $apartment1 = Apartment::firstOrCreate(
            [
                'community_id' => $community->id,
                'apartment_number' => '1',
            ],
            [
                'building_number' => null,
                'area' => 45.50,
                'heated_area' => 45.50,
                'common_area_share' => 5.0,
                'floor' => 0,
                'is_owned' => true,
                'is_commercial' => false,
            ]
        );

        $apartment2 = Apartment::firstOrCreate(
            [
                'community_id' => $community->id,
                'apartment_number' => '2',
            ],
            [
                'building_number' => null,
                'area' => 62.30,
                'heated_area' => 62.30,
                'common_area_share' => 7.5,
                'floor' => 0,
                'is_owned' => true,
                'is_commercial' => false,
            ]
        );

        // Create test persons
        $person1 = Person::firstOrCreate(
            ['email' => 'owner1@test.com'],
            [
                'first_name' => 'Jan',
                'last_name' => 'Kowalski',
                'phone' => '+48 123 456 789',
                'correspondence_address_street' => 'ul. Testowa 1/1',
                'correspondence_address_postal_code' => '00-001',
                'correspondence_address_city' => 'Warszawa',
                'is_active' => true,
                'ownership_share' => 100.0,
            ]
        );

        $person2 = Person::firstOrCreate(
            ['email' => 'owner2@test.com'],
            [
                'first_name' => 'Anna',
                'last_name' => 'Nowak',
                'phone' => '+48 987 654 321',
                'correspondence_address_street' => 'ul. Testowa 1/2',
                'correspondence_address_postal_code' => '00-001',
                'correspondence_address_city' => 'Warszawa',
                'is_active' => true,
                'ownership_share' => 100.0,
            ]
        );

        // Link persons to apartments
        $apartment1->people()->syncWithoutDetaching([
            $person1->id => [
                'ownership_share' => 100.0,
                'is_primary' => true,
            ]
        ]);

        $apartment2->people()->syncWithoutDetaching([
            $person2->id => [
                'ownership_share' => 100.0,
                'is_primary' => true,
            ]
        ]);

        // Create test owner users
        $user1 = User::firstOrCreate(
            ['email' => 'owner1@test.com'],
            [
                'name' => 'Jan Kowalski',
                'first_name' => 'Jan',
                'last_name' => 'Kowalski',
                'password' => Hash::make('password123'),
                'user_type' => 'owner',
                'phone' => '+48 123 456 789',
                'two_factor_enabled' => true,
                'two_factor_method' => 'email',
                'is_active' => true,
                'email_verified_at' => now(),
                'person_id' => $person1->id,
            ]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'owner2@test.com'],
            [
                'name' => 'Anna Nowak',
                'first_name' => 'Anna',
                'last_name' => 'Nowak',
                'password' => Hash::make('password123'),
                'user_type' => 'owner',
                'phone' => '+48 987 654 321',
                'two_factor_enabled' => true,
                'two_factor_method' => 'sms',
                'is_active' => true,
                'email_verified_at' => now(),
                'person_id' => $person2->id,
            ]
        );

        // Link users to communities
        $user1->communities()->syncWithoutDetaching([
            $community->id => [
                'access_type' => 'owner',
                'is_active' => true,
                'verified_at' => now(),
            ]
        ]);

        $user2->communities()->syncWithoutDetaching([
            $community->id => [
                'access_type' => 'owner',
                'is_active' => true,
                'verified_at' => now(),
            ]
        ]);

        $this->command->info('Created test users:');
        $this->command->info('1. Email: owner1@test.com | Password: password123 | 2FA: Email');
        $this->command->info('2. Email: owner2@test.com | Password: password123 | 2FA: SMS');
        $this->command->info('Both users own apartments in "Test Community"');
    }
}