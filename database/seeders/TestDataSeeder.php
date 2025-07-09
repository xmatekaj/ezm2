<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\Apartment;
use App\Models\Person;
use App\Models\WaterMeter;
use App\Models\BankAccount;
use App\Models\Price;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test community
        $community = Community::factory()->create([
            'name' => 'WM "Narutowicza"',
            'full_name' => 'Wspólnota Mieszkaniowa przy ul. Narutowicza 15',
            'address_city' => 'Katowice',
            'apartment_count' => 12,
        ]);

        // Create apartments
        $apartments = Apartment::factory(12)->create([
            'community_id' => $community->id,
        ]);

        // Create people
        $people = Person::factory(20)->create();

        // Create bank account for community
        BankAccount::create([
            'account_number' => '12123456789012345678901234',
            'bank_name' => 'PKO Bank Polski',
            'is_active' => true,
            'community_id' => $community->id,
        ]);

        // Create current prices
        Price::create([
            'change_date' => now(),
            'water_sewage_price' => 12.50,
            'garbage_price' => 45.00,
            'management_fee' => 2.80,
            'renovation_fund' => 1.50,
            'loan_fund' => 0.50,
            'central_heating_advance' => 180.00,
            'community_id' => $community->id,
        ]);

        // Assign people to apartments
        foreach ($apartments as $index => $apartment) {
            $person = $people[$index % $people->count()];

            $apartment->people()->attach($person->id, [
                'ownership_share' => 100.00,
                'is_primary' => true,
            ]);

            // Add water meter to apartment
            WaterMeter::create([
                'installation_date' => now()->subMonths(rand(1, 24)),
                'meter_expiry_date' => now()->addYears(6),
                'meter_number' => 100000 + $apartment->id,
                'transmitter_number' => 200000 + $apartment->id,
                'is_active' => true,
                'apartment_id' => $apartment->id,
            ]);
        }

        $this->command->info('Test data created successfully!');
        $this->command->info("Community: {$community->name}");
        $this->command->info("Apartments: {$apartments->count()}");
        $this->command->info("People: {$people->count()}");
    }
}
