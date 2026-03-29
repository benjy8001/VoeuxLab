<?php

use App\Models\{Couple, User, VowsDraft};

test('user can set tone on their draft', function () {
    $user = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $user->id]);
    $user->update(['couple_id' => $couple->id]);
    VowsDraft::factory()->create(['user_id' => $user->id, 'couple_id' => $couple->id]);

    $this->actingAs($user)
        ->post(route('voeux.tone'), ['tone' => 'poetic'])
        ->assertRedirect();

    expect(VowsDraft::where('user_id', $user->id)->first()->tone)->toBe('poetic');
});

test('invalid tone is rejected', function () {
    $user = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $user->id]);
    $user->update(['couple_id' => $couple->id]);
    VowsDraft::factory()->create(['user_id' => $user->id, 'couple_id' => $couple->id]);

    $this->actingAs($user)
        ->post(route('voeux.tone'), ['tone' => 'invalid'])
        ->assertSessionHasErrors('tone');
});

test('questions vary by tone', function () {
    $questions = \App\Support\VowsQuestions::forTone('poetic');
    $default   = \App\Support\VowsQuestions::forTone('balanced');

    $poeticQ   = collect($questions)->firstWhere('key', 'admired_quality');
    $balancedQ = collect($default)->firstWhere('key', 'admired_quality');

    expect($poeticQ['label'])->not->toBe($balancedQ['label']);
});
