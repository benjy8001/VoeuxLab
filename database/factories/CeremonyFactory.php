<?php

namespace Database\Factories;

use App\Models\Couple;
use Illuminate\Database\Eloquent\Factories\Factory;

class CeremonyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'couple_id' => Couple::factory(),
            'program'   => [],
            'status'    => 'draft',
        ];
    }
}
