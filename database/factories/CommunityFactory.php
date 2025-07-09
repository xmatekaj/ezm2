<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CommunityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'WM "' . $this->faker->streetName . '"',
            'full_name' => 'Wspólnota Mieszkaniowa przy ul. ' . $this->faker->streetAddress,
            'address_street' => 'ul. ' . $this->faker->streetName . ' ' . $this->faker->buildingNumber,
            'address_postal_code' => $this->faker->postcode,
            'address_city' => $this->faker->city,
            'address_state' => 'śląskie',
            'regon' => $this->faker->numerify('#########'),
            'tax_id' => $this->faker->numerify('##########'),
            'manager_name' => $this->faker->company . ' Sp. z o.o.',
            'manager_address_street' => 'ul. ' . $this->faker->streetName . ' ' . $this->faker->buildingNumber,
            'manager_address_postal_code' => $this->faker->postcode,
            'manager_address_city' => $this->faker->city,
            'common_area_size' => $this->faker->randomFloat(2, 100, 500),
            'apartments_area' => $this->faker->randomFloat(2, 1000, 5000),
            'is_active' => true,
            'has_elevator' => $this->faker->boolean(60),
            'apartment_count' => $this->faker->numberBetween(10, 50),
            'staircase_count' => $this->faker->numberBetween(1, 3),
            'color' => $this->faker->hexColor,
        ];
    }
}
