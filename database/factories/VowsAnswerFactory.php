<?php

namespace Database\Factories;

use App\Models\VowsDraft;
use Illuminate\Database\Eloquent\Factories\Factory;

class VowsAnswerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vows_draft_id' => VowsDraft::factory(),
            'question_key' => fake()->unique()->word(),
            'answer_text' => fake()->paragraph(),
            'step_order' => fake()->numberBetween(1, 10),
        ];
    }
}
