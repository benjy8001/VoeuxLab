<?php

use App\Models\Couple;
use App\Models\User;
use App\Models\VowsDraft;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('retourne false si les deux époux n\'ont pas partagé', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => Carbon::tomorrow(),
    ]);
    $myDraft = VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => null,
    ]);
    VowsDraft::factory()->create([
        'user_id'   => $spouse2->id,
        'couple_id' => $couple->id,
        'shared_at' => null,
    ]);

    expect($myDraft->isReadableByPartner($couple))->toBeFalse();
});

test('retourne false si seulement moi ai partagé', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => Carbon::tomorrow(),
    ]);
    $myDraft = VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);
    VowsDraft::factory()->create([
        'user_id'   => $spouse2->id,
        'couple_id' => $couple->id,
        'shared_at' => null,
    ]);

    expect($myDraft->isReadableByPartner($couple))->toBeFalse();
});

test('retourne true si les deux époux ont partagé', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => Carbon::tomorrow(),
    ]);
    $myDraft = VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);
    VowsDraft::factory()->create([
        'user_id'   => $spouse2->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);

    expect($myDraft->isReadableByPartner($couple))->toBeTrue();
});

test('retourne true si la date de cérémonie est passée', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => Carbon::yesterday(),
    ]);
    $myDraft = VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => null,
    ]);

    expect($myDraft->isReadableByPartner($couple))->toBeTrue();
});

test('retourne false si le partenaire n\'a pas encore rejoint le couple', function () {
    $spouse1 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => null,
        'ceremony_date' => Carbon::tomorrow(),
    ]);
    $myDraft = VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);

    expect($myDraft->isReadableByPartner($couple))->toBeFalse();
});

test('retourne true si la date de cérémonie est aujourd\'hui', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => Carbon::today(),
    ]);
    $myDraft = VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => null,
    ]);

    expect($myDraft->isReadableByPartner($couple))->toBeTrue();
});

test('retourne true depuis la perspective de spouse2 si les deux ont partagé', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => Carbon::tomorrow(),
    ]);
    VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);
    $spouse2Draft = VowsDraft::factory()->create([
        'user_id'   => $spouse2->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);

    expect($spouse2Draft->isReadableByPartner($couple))->toBeTrue();
});
