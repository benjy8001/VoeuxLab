<?php

namespace Database\Factories;

use App\Models\Couple;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VowsDraftFactory extends Factory
{
    public function definition(): array
    {
        $user = User::factory()->create();
        $couple = Couple::factory()->create(['spouse_1_id' => $user->id]);

        return [
            'user_id' => $user->id,
            'couple_id' => $couple->id,
            'status' => 'in_progress',
            'current_step' => 1,
        ];
    }
}
