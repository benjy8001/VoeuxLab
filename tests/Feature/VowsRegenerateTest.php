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

test('user peut régénérer ses vœux et generated_text est effacé', function () {
    $user = userWithCompleteDraft();
    $draft = VowsDraft::where('user_id', $user->id)->first();
    $draft->update(['generated_text' => 'texte existant']);

    $this->actingAs($user)
        ->post(route('voeux.regenerate'))
        ->assertRedirect(route('voeux.preview'));

    expect($draft->fresh()->generated_text)->toBeNull();
});

test('user ne peut pas régénérer le draft d\'un autre', function () {
    $owner = userWithCompleteDraft();

    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->post(route('voeux.regenerate'))
        ->assertStatus(404);
});
