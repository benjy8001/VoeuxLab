<?php

use App\Models\Couple;
use App\Models\User;
use App\Models\VowsDraft;
use Illuminate\Support\Carbon;

test('POST /voeux/share renseigne shared_at sur le draft', function () {
    $user = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $user->id]);
    $user->update(['couple_id' => $couple->id]);
    $draft = VowsDraft::factory()->create([
        'user_id'   => $user->id,
        'couple_id' => $couple->id,
        'shared_at' => null,
    ]);

    $this->actingAs($user)
        ->post(route('voeux.share'))
        ->assertRedirect(route('voeux.preview'));

    expect($draft->fresh()->shared_at)->not->toBeNull();
});

test('POST /voeux/share est idempotent (ne change pas shared_at si déjà renseigné)', function () {
    $user = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $user->id]);
    $user->update(['couple_id' => $couple->id]);
    $sharedAt = Carbon::parse('2026-01-01 10:00:00');
    $draft = VowsDraft::factory()->create([
        'user_id'   => $user->id,
        'couple_id' => $couple->id,
        'shared_at' => $sharedAt,
    ]);

    $this->actingAs($user)
        ->post(route('voeux.share'));

    expect($draft->fresh()->shared_at->toDateTimeString())->toBe($sharedAt->toDateTimeString());
});

test('GET /voeux/partner retourne 403 si le partenaire n\'a pas encore partagé', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => Carbon::tomorrow(),
    ]);
    $spouse1->update(['couple_id' => $couple->id]);
    VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);
    VowsDraft::factory()->create([
        'user_id'        => $spouse2->id,
        'couple_id'      => $couple->id,
        'shared_at'      => null,
        'generated_text' => 'vœux partenaire',
    ]);

    $this->actingAs($spouse1)
        ->get(route('voeux.partner'))
        ->assertForbidden();
});

test('GET /voeux/partner retourne le texte du partenaire si les deux ont partagé', function () {
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();
    $couple = Couple::factory()->create([
        'spouse_1_id'   => $spouse1->id,
        'spouse_2_id'   => $spouse2->id,
        'ceremony_date' => Carbon::tomorrow(),
    ]);
    $spouse1->update(['couple_id' => $couple->id]);
    VowsDraft::factory()->create([
        'user_id'   => $spouse1->id,
        'couple_id' => $couple->id,
        'shared_at' => now(),
    ]);
    VowsDraft::factory()->create([
        'user_id'        => $spouse2->id,
        'couple_id'      => $couple->id,
        'shared_at'      => now(),
        'generated_text' => 'vœux du partenaire',
    ]);

    $this->actingAs($spouse1)
        ->get(route('voeux.partner'))
        ->assertInertia(fn ($page) => $page
            ->component('Voeux/Partner')
            ->where('generated_text', 'vœux du partenaire')
        );
});
