<?php

use App\Models\Couple;
use App\Models\User;
use App\Models\VowsDraft;
use App\Models\VowsAnswer;

test('couple generates invitation code on creation', function () {
    $user = User::factory()->create();
    $couple = Couple::create(['spouse_1_id' => $user->id]);

    expect($couple->invitation_code)->toHaveLength(8);
    expect($couple->invitation_code)->toMatch('/^[A-Z0-9]{8}$/');
});

test('couple isFull returns true when both spouses set', function () {
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_2_id' => $spouse2->id,
    ]);

    expect($couple->isFull())->toBeTrue();
});

test('couple isFull returns false when spouse2 is null', function () {
    $couple = Couple::factory()->create(['spouse_2_id' => null]);

    expect($couple->isFull())->toBeFalse();
});

test('vows draft belongs to user', function () {
    $draft = VowsDraft::factory()->create();

    expect($draft->user)->toBeInstanceOf(User::class);
});

test('vows draft has many answers', function () {
    $draft = VowsDraft::factory()->create();
    VowsAnswer::factory()->count(3)->create(['vows_draft_id' => $draft->id]);

    expect($draft->answers)->toHaveCount(3);
});

test('user has couple relation', function () {
    $user = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $user->id]);
    $user->update(['couple_id' => $couple->id]);

    expect($user->couple)->toBeInstanceOf(Couple::class);
});
