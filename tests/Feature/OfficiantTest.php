<?php

use App\Models\{Couple, OfficiantDraft, User};

test('user can create an officiant draft linked to a couple', function () {
    $spouse1 = User::factory()->create();
    $couple  = Couple::factory()->create(['spouse_1_id' => $spouse1->id]);
    $spouse1->update(['couple_id' => $couple->id]);

    $officiant = User::factory()->create();

    $response = $this->actingAs($officiant)
        ->post(route('officiant.store'), [
            'invitation_code' => $couple->invitation_code,
        ]);

    $response->assertRedirect(route('officiant.index'));
    expect(OfficiantDraft::where('user_id', $officiant->id)->exists())->toBeTrue();
});

test('officiant can save an answer', function () {
    $officiant = User::factory()->create();
    $couple    = Couple::factory()->create();
    $draft     = OfficiantDraft::factory()->create([
        'user_id'   => $officiant->id,
        'couple_id' => $couple->id,
    ]);

    $this->actingAs($officiant)
        ->post(route('officiant.answer'), [
            'question_key' => 'couple_link',
            'answer_text'  => 'Je suis l\'ami d\'enfance de Pierre.',
            'current_step' => 2,
            'final'        => false,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('officiant_answers', [
        'officiant_draft_id' => $draft->id,
        'question_key'       => 'couple_link',
    ]);
});

test('another user cannot view an officiant draft', function () {
    $officiant = User::factory()->create();
    $couple    = Couple::factory()->create();
    $draft     = OfficiantDraft::factory()->create([
        'user_id'   => $officiant->id,
        'couple_id' => $couple->id,
    ]);

    $intruder = User::factory()->create();

    expect($intruder->can('view', $draft))->toBeFalse();
});
