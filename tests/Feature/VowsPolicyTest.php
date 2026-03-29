<?php

use App\Models\Couple;
use App\Models\User;
use App\Models\VowsDraft;

test('user can view their own vows draft', function () {
    $user = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $user->id]);
    $draft = VowsDraft::factory()->create(['user_id' => $user->id, 'couple_id' => $couple->id]);

    expect($user->can('view', $draft))->toBeTrue();
});

test('user cannot view another user vows draft', function () {
    $owner = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $owner->id]);
    $draft = VowsDraft::factory()->create(['user_id' => $owner->id, 'couple_id' => $couple->id]);

    $intruder = User::factory()->create();

    expect($intruder->can('view', $draft))->toBeFalse();
});

test('user can update their own vows draft', function () {
    $user = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $user->id]);
    $draft = VowsDraft::factory()->create(['user_id' => $user->id, 'couple_id' => $couple->id]);

    expect($user->can('update', $draft))->toBeTrue();
});

test('user cannot update another user vows draft', function () {
    $owner = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $owner->id]);
    $draft = VowsDraft::factory()->create(['user_id' => $owner->id, 'couple_id' => $couple->id]);

    $intruder = User::factory()->create();

    expect($intruder->can('update', $draft))->toBeFalse();
});
