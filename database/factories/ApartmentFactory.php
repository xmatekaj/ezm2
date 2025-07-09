<?php

namespace Database\Factories;

use App\Models\Community;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'building_number' => $this->faker->optional(0.7)->numberBetween(1, 5),
            'apartment_number' => $this->faker->numberBetween(1, 100),
            'area' => $this->faker->randomFloat(2, 25, 120),
            'basement_area' => $this->faker->optional(0.4)->randomFloat(2, 3, 15),
            'storage_area' => $this->faker->optional(0.3)->randomFloat(2, 2, 8),
            'heated_area' => $this->faker->randomFloat(2, 20, 110),
            'common_area_share' => $this->faker->randomFloat(2, 1, 5),
            'floor' => $this->faker->numberBetween(0, 10),
            'elevator_fee_coefficient' => $this->faker->randomFloat(2, 0.5, 1.5),
            'has_basement' => $this->faker->boolean(40),
            'has_storage' => $this->faker->boolean(30),
            'is_owned' => $this->faker->boolean(90),
            'is_commercial' => $this->faker->boolean(10),
            'community_id' => Community::factory(),
        ];
    }
}
