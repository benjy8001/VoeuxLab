<?php

use App\Models\Couple;
use App\Models\User;
use App\Models\VowsAnswer;
use App\Models\VowsDraft;
use App\Support\VowsQuestions;

test('user with couple can access vows journey', function () {
    $user = userWithDraft();

    $this->actingAs($user)
        ->get(route('voeux.index'))
        ->assertInertia(fn ($page) => $page
            ->component('Voeux/Index')
            ->has('questions', VowsQuestions::count())
            ->has('draft.current_step')
        );
});

test('user without couple is redirected to couple create', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('voeux.index'))
        ->assertRedirect(route('couple.create'));
});

test('user can save a vows answer', function () {
    $user = userWithDraft();

    $this->actingAs($user)->post(route('voeux.answer'), [
        'question_key' => 'meeting_story',
        'answer_text' => 'Nous nous sommes rencontrés à Paris.',
        'current_step' => 1,
        'final' => false,
    ]);

    $draft = VowsDraft::where('user_id', $user->id)->first();
    $this->assertDatabaseHas('vows_answers', [
        'vows_draft_id' => $draft->id,
        'question_key' => 'meeting_story',
        'answer_text' => 'Nous nous sommes rencontrés à Paris.',
    ]);
});

test('saving with final=true redirects to preview', function () {
    $user = userWithDraft();

    $this->actingAs($user)
        ->post(route('voeux.answer'), [
            'question_key' => 'closing_words',
            'answer_text' => 'Je t\'aime.',
            'current_step' => 14,
            'final' => true,
        ])
        ->assertRedirect(route('voeux.preview'));
});

test('preview passes questions and answers to inertia', function () {
    $user = userWithDraft();
    $draft = VowsDraft::where('user_id', $user->id)->first();

    VowsAnswer::factory()->create([
        'vows_draft_id' => $draft->id,
        'question_key'  => 'meeting_story',
        'answer_text'   => 'Nous nous sommes rencontrés à Paris.',
        'step_order'    => 1,
    ]);

    $draft->update(['generated_text' => 'Vœux générés.', 'status' => 'completed']);

    $this->actingAs($user)
        ->get(route('voeux.preview'))
        ->assertInertia(fn ($page) => $page
            ->component('Voeux/Preview')
            ->has('questions', VowsQuestions::count())
            ->has('answers.meeting_story')
        );
});

test('export vows pdf returns a download with answers', function () {
    $user = userWithDraft();
    $draft = VowsDraft::where('user_id', $user->id)->first();

    VowsAnswer::factory()->create([
        'vows_draft_id' => $draft->id,
        'question_key'  => 'meeting_story',
        'answer_text'   => 'Nous nous sommes rencontrés.',
        'step_order'    => 1,
    ]);

    $draft->update(['generated_text' => 'Vœux.', 'status' => 'completed']);

    $this->actingAs($user)
        ->get(route('voeux.export'))
        ->assertStatus(200)
        ->assertHeader('content-type', 'application/pdf');
});

test('user cannot save answers for another user draft', function () {
    $owner = userWithDraft();
    $ownerDraft = VowsDraft::where('user_id', $owner->id)->first();

    $intruder = userWithDraft();

    // Intruder tries to post to their own endpoint — they get their own draft not owner's
    // Direct policy test: intruder cannot update owner's draft
    expect($intruder->can('update', $ownerDraft))->toBeFalse();
});
