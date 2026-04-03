<?php

use App\Models\Couple;
use App\Models\User;
use App\Models\VowsDraft;

function userWithCompleteDraft(): User
{
    $user = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $user->id]);
    $user->update(['couple_id' => $couple->id]);
    VowsDraft::factory()->create([
        'user_id'   => $user->id,
        'couple_id' => $couple->id,
        'status'    => 'completed',
    ]);
    return $user;
}

test('user can regenerate their vows and generated_text is cleared', function () {
    $user = userWithCompleteDraft();
    $draft = VowsDraft::where('user_id', $user->id)->first();
    $draft->update(['generated_text' => 'texte existant']);

    $this->actingAs($user)
        ->post(route('voeux.regenerate'))
        ->assertRedirect(route('voeux.preview'));

    expect($draft->fresh()->generated_text)->toBeNull();
});

test('user cannot regenerate another user draft', function () {
    $owner = userWithCompleteDraft();
    $couple = Couple::factory()->create(['spouse_1_id' => $owner->id]);
    VowsDraft::factory()->create([
        'user_id'   => $owner->id,
        'couple_id' => $couple->id,
        'status'    => 'completed',
    ]);

    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->post(route('voeux.regenerate'))
        ->assertStatus(404);
});
