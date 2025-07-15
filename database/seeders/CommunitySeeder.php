<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Community;

class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        $communities = [
            [
                'name' => 'WM "Słoneczna"',
                'full_name' => 'Wspólnota Mieszkaniowa przy ul. Słonecznej 15',
                'internal_code' => 'WMS001',
                'address_street' => 'ul. Słoneczna 15',
                'address_postal_code' => '40-001',
                'address_city' => 'Katowice',
                'address_state' => 'śląskie',
                'regon' => '123456789',
                'tax_id' => '1234567890',
                'total_area' => 1750.25,
                'apartments_area' => 1500.75,
                'apartment_count' => 24,
                'staircase_count' => 2,
                'has_elevator' => true,
                'residential_water_meters' => 24,
                'main_water_meters' => 2,
                'is_active' => true,
                'color' => '#3b82f6',
            ],
            [
                'name' => 'WM "Parkowa"',
                'full_name' => 'Wspólnota Mieszkaniowa przy ul. Parkowej 8',
                'internal_code' => 'WMP002',
                'address_street' => 'ul. Parkowa 8',
                'address_postal_code' => '40-010',
                'address_city' => 'Katowice',
                'address_state' => 'śląskie',
                'regon' => null, // Example of optional field
                'tax_id' => null, // Example of optional field
                'total_area' => 2250.50,
                'apartments_area' => 2000.00,
                'apartment_count' => 32,
                'staircase_count' => 3,
                'has_elevator' => false,
                'residential_water_meters' => 32,
                'main_water_meters' => 3,
                'is_active' => true,
                'color' => '#10b981',
            ],
            [
                'name' => 'WM "Centralna"',
                'full_name' => 'Wspólnota Mieszkaniowa Centralna',
                'internal_code' => 'WMC003',
                'address_street' => 'ul. Centralna 22',
                'address_postal_code' => '40-020',
                'address_city' => 'Katowice',
                'address_state' => 'śląskie',
                'regon' => '987654321',
                'tax_id' => '9876543210',
                'total_area' => null, // Example of optional technical parameter
                'apartments_area' => null,
                'apartment_count' => null,
                'staircase_count' => null,
                'has_elevator' => true,
                'residential_water_meters' => null,
                'main_water_meters' => null,
                'is_active' => true,
                'color' => '#f59e0b',
            ],
        ];

        foreach ($communities as $community) {
            Community::create($community);
        }
    }
}
