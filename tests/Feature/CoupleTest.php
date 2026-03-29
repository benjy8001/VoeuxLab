<?php

use App\Models\Couple;
use App\Models\User;
use App\Models\VowsDraft;

test('authenticated user can create a couple', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('couple.store'), [
        'ceremony_date' => '2027-06-14',
        'ceremony_location' => 'Château de Versailles',
    ]);

    $response->assertRedirect(route('couple.show'));
    expect(Couple::where('spouse_1_id', $user->id)->exists())->toBeTrue();
    expect(VowsDraft::where('user_id', $user->id)->exists())->toBeTrue();
    expect($user->fresh()->couple_id)->not->toBeNull();
});

test('user cannot create a couple if already in one', function () {
    $couple = Couple::factory()->create();
    $user = User::factory()->create(['couple_id' => $couple->id]);

    $this->actingAs($user)->post(route('couple.store'))->assertStatus(403);
});

test('user can join a couple via invitation code', function () {
    $spouse1 = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $spouse1->id]);
    $spouse1->update(['couple_id' => $couple->id]);

    $spouse2 = User::factory()->create();

    $response = $this->actingAs($spouse2)->post(route('couple.attach'), [
        'invitation_code' => $couple->invitation_code,
    ]);

    $response->assertRedirect(route('couple.show'));
    expect($couple->fresh()->spouse_2_id)->toBe($spouse2->id);
    expect($spouse2->fresh()->couple_id)->toBe($couple->id);
    expect(VowsDraft::where('user_id', $spouse2->id)->exists())->toBeTrue();
});

test('joining a full couple is rejected', function () {
    $spouse2user = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_2_id' => $spouse2user->id,
    ]);
    $outsider = User::factory()->create();

    $this->actingAs($outsider)->post(route('couple.attach'), [
        'invitation_code' => $couple->invitation_code,
    ])->assertStatus(422);
});

test('unauthenticated user cannot create couple', function () {
    $this->post(route('couple.store'))->assertRedirect(route('login'));
});
