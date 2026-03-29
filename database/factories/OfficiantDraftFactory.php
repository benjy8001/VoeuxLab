<?php

namespace Database\Factories;

use App\Models\{Couple, User};
use Illuminate\Database\Eloquent\Factories\Factory;

class OfficiantDraftFactory extends Factory
{
    public function definition(): array
    {
        $user   = User::factory()->create();
        $couple = Couple::factory()->create();

        return [
            'user_id'      => $user->id,
            'couple_id'    => $couple->id,
            'status'       => 'in_progress',
            'current_step' => 1,
        ];
    }
}
