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

test('un époux peut consulter les vœux du partenaire si les deux ont partagé', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => \Illuminate\Support\Carbon::tomorrow(),
    ]);
    $spouse1->update(['couple_id' => $couple->id]);
    VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);
    $partnerDraft = VowsDraft::factory()->create([
        'user_id'   => $spouse2->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);

    expect($spouse1->can('viewPartner', $partnerDraft))->toBeTrue();
});

test('un époux ne peut pas consulter les vœux du partenaire si l\'un d\'eux n\'a pas partagé', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => \Illuminate\Support\Carbon::tomorrow(),
    ]);
    $spouse1->update(['couple_id' => $couple->id]);
    VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);
    $partnerDraft = VowsDraft::factory()->create([
        'user_id'   => $spouse2->id,
        'couple_id' => $couple->id,
        'shared_at' => null,
    ]);

    expect($spouse1->can('viewPartner', $partnerDraft))->toBeFalse();
});

test('un tiers ne peut jamais accéder aux vœux du partenaire', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id' => $spouse1->id,
        'spouse_2_id' => $spouse2->id,
    ]);
    $partnerDraft = VowsDraft::factory()->create([
        'user_id'   => $spouse2->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);
    $intruder = User::factory()->create();

    expect($intruder->can('viewPartner', $partnerDraft))->toBeFalse();
});
