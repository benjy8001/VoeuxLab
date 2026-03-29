<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CoupleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'spouse_1_id' => User::factory(),
            'ceremony_date' => fake()->dateTimeBetween('+1 month', '+2 years')->format('Y-m-d'),
            'ceremony_location' => fake()->city() . ', France',
        ];
    }
}
