<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->numerify('+48 ### ### ###'),
            'correspondence_address_street' => 'ul. ' . $this->faker->streetName . ' ' . $this->faker->buildingNumber,
            'correspondence_address_postal_code' => $this->faker->postcode,
            'correspondence_address_city' => $this->faker->city,
            'is_active' => true,
            'notes' => $this->faker->optional(0.3)->sentence,
        ];
    }
}
