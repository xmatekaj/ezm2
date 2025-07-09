<?php

namespace Database\Seeders;

use App\Models\Community;
use Illuminate\Database\Seeder;

class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        Community::create([
            'name' => 'Wspólnota Mieszkaniowa "Narutowicza"',
            'full_name' => 'Wspólnota Mieszkaniowa przy ul. Narutowicza 15',
            'address_street' => 'ul. Narutowicza 15',
            'address_postal_code' => '40-016',
            'address_city' => 'Katowice',
            'address_state' => 'śląskie',
            'regon' => '123456789',
            'tax_id' => '1234567890',
            'manager_name' => 'Zarządca ABC Sp. z o.o.',
            'manager_address_street' => 'ul. Zarządu 1',
            'manager_address_postal_code' => '40-001',
            'manager_address_city' => 'Katowice',
            'common_area_size' => 250.50,
            'apartments_area' => 1500.75,
            'has_elevator' => true,
            'apartment_count' => 24,
            'staircase_count' => 2,
        ]);
    }
}
