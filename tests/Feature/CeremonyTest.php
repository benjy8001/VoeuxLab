<?php

use App\Models\Ceremony;
use App\Models\Couple;
use App\Models\OfficiantDraft;
use App\Models\User;

// ─── Helpers locaux ────────────────────────────────────────────────────────────

/**
 * Crée un couple complet avec sa cérémonie et retourne [époux1, époux2, cérémonie].
 */
function coupleWithCeremony(): array
{
    $spouse1 = User::factory()->create();
    $spouse2 = User::factory()->create();

    $couple = Couple::factory()->create([
        'spouse_1_id' => $spouse1->id,
        'spouse_2_id' => $spouse2->id,
    ]);

    $spouse1->update(['couple_id' => $couple->id]);
    $spouse2->update(['couple_id' => $couple->id]);

    $ceremony = Ceremony::factory()->create([
        'couple_id' => $couple->id,
        'program'   => [],
        'status'    => 'draft',
    ]);

    return [$spouse1, $spouse2, $ceremony];
}

/**
 * Crée un officiant pour un couple donné.
 */
function officiantFor(Couple $couple): User
{
    $officiant = User::factory()->create();
    OfficiantDraft::factory()->create([
        'user_id'  => $officiant->id,
        'couple_id' => $couple->id,
    ]);
    return $officiant;
}

// ─── Policy : view ─────────────────────────────────────────────────────────────

test('CeremonyPolicy::view — époux 1 peut voir la cérémonie', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();

    expect($spouse1->can('view', $ceremony))->toBeTrue();
});

test('CeremonyPolicy::view — époux 2 peut voir la cérémonie', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();

    expect($spouse2->can('view', $ceremony))->toBeTrue();
});

test('CeremonyPolicy::view — officiant peut voir la cérémonie', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();
    $officiant = officiantFor($spouse1->couple);

    expect($officiant->can('view', $ceremony))->toBeTrue();
});

test('CeremonyPolicy::view — tiers ne peut pas voir la cérémonie', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();
    $outsider = User::factory()->create();

    expect($outsider->can('view', $ceremony))->toBeFalse();
});

// ─── Policy : updateBlocks ─────────────────────────────────────────────────────

test('CeremonyPolicy::updateBlocks — époux peut modifier les blocs', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();

    expect($spouse1->can('updateBlocks', $ceremony))->toBeTrue();
});

test('CeremonyPolicy::updateBlocks — officiant ne peut pas modifier les blocs', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();
    $officiant = officiantFor($spouse1->couple);

    expect($officiant->can('updateBlocks', $ceremony))->toBeFalse();
});

// ─── Policy : updateOfficiantNote ──────────────────────────────────────────────

test('CeremonyPolicy::updateOfficiantNote — officiant peut annoter', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();
    $officiant = officiantFor($spouse1->couple);

    expect($officiant->can('updateOfficiantNote', $ceremony))->toBeTrue();
});

test('CeremonyPolicy::updateOfficiantNote — époux ne peut pas annoter', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();

    expect($spouse1->can('updateOfficiantNote', $ceremony))->toBeFalse();
});

// ─── POST /ceremonie/blocks — ajouter un bloc ──────────────────────────────────

test('un époux peut ajouter un bloc à la cérémonie', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();

    $response = $this->actingAs($spouse1)->post(route('ceremony.blocks.add'), [
        'type'             => 'entrance',
        'title'            => 'Entrée des mariés',
        'time'             => '14h00',
        'duration_minutes' => 5,
        'description'      => 'Depuis l\'allée ouest.',
    ]);

    $response->assertRedirect();
    $ceremony->refresh();
    expect($ceremony->program)->toHaveCount(1);
    expect($ceremony->program[0]['type'])->toBe('entrance');
    expect($ceremony->program[0]['title'])->toBe('Entrée des mariés');
    expect($ceremony->program[0])->toHaveKey('id');
});

test('un officiant ne peut pas ajouter un bloc (403)', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();
    $officiant = officiantFor($spouse1->couple);

    $this->actingAs($officiant)->post(route('ceremony.blocks.add'), [
        'type'  => 'welcome',
        'title' => 'Bienvenue',
    ])->assertStatus(403);
});

test('un tiers ne peut pas ajouter un bloc (403)', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)->post(route('ceremony.blocks.add'), [
        'type'  => 'welcome',
        'title' => 'Test',
    ])->assertStatus(403);
});

// ─── DELETE /ceremonie/blocks/{id} — supprimer un bloc ─────────────────────────

test('un époux peut supprimer un bloc de la cérémonie', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();

    $blockId = \Illuminate\Support\Str::uuid()->toString();
    $ceremony->update([
        'program' => [
            ['id' => $blockId, 'type' => 'reading', 'title' => 'Lecture', 'time' => null,
             'duration_minutes' => null, 'description' => null, 'notes_officiant' => null],
        ],
    ]);

    $response = $this->actingAs($spouse1)->delete(route('ceremony.blocks.remove', $blockId));

    $response->assertRedirect();
    $ceremony->refresh();
    expect($ceremony->program)->toBeEmpty();
});

test('un officiant ne peut pas supprimer un bloc (403)', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();
    $officiant = officiantFor($spouse1->couple);

    $blockId = \Illuminate\Support\Str::uuid()->toString();
    $ceremony->update([
        'program' => [
            ['id' => $blockId, 'type' => 'reading', 'title' => 'Lecture', 'time' => null,
             'duration_minutes' => null, 'description' => null, 'notes_officiant' => null],
        ],
    ]);

    $this->actingAs($officiant)->delete(route('ceremony.blocks.remove', $blockId))->assertStatus(403);
});

// ─── PATCH /ceremonie/blocks/{id}/notes — annoter un bloc ─────────────────────

test('un officiant peut annoter un bloc', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();
    $officiant = officiantFor($spouse1->couple);

    $blockId = \Illuminate\Support\Str::uuid()->toString();
    $ceremony->update([
        'program' => [
            ['id' => $blockId, 'type' => 'vows', 'title' => 'Vœux', 'time' => null,
             'duration_minutes' => null, 'description' => null, 'notes_officiant' => null],
        ],
    ]);

    $response = $this->actingAs($officiant)->patch(
        route('ceremony.blocks.notes', $blockId),
        ['notes_officiant' => 'Attendre le silence complet.']
    );

    $response->assertRedirect();
    $ceremony->refresh();
    expect($ceremony->program[0]['notes_officiant'])->toBe('Attendre le silence complet.');
});

test('un époux ne peut pas modifier notes_officiant d\'un bloc (403)', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();

    $blockId = \Illuminate\Support\Str::uuid()->toString();
    $ceremony->update([
        'program' => [
            ['id' => $blockId, 'type' => 'vows', 'title' => 'Vœux', 'time' => null,
             'duration_minutes' => null, 'description' => null, 'notes_officiant' => null],
        ],
    ]);

    $this->actingAs($spouse1)->patch(
        route('ceremony.blocks.notes', $blockId),
        ['notes_officiant' => 'Tentative piratage']
    )->assertStatus(403);
});

// ─── Auto-création à la formation du couple ────────────────────────────────────

test('une cérémonie est automatiquement créée lors de la formation du couple', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('couple.store'), [
        'ceremony_date'     => '2027-06-14',
        'ceremony_location' => 'Château de Versailles',
    ]);

    $couple = $user->fresh()->couple;
    expect($couple)->not->toBeNull();
    expect(Ceremony::where('couple_id', $couple->id)->exists())->toBeTrue();
});

// ─── PUT /ceremonie/blocks — réordonner les blocs ─────────────────────────────

test('un époux peut réordonner et éditer les blocs', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();

    $id1 = \Illuminate\Support\Str::uuid()->toString();
    $id2 = \Illuminate\Support\Str::uuid()->toString();

    $ceremony->update([
        'program' => [
            ['id' => $id1, 'type' => 'entrance', 'title' => 'Entrée', 'time' => null,
             'duration_minutes' => null, 'description' => null, 'notes_officiant' => null],
            ['id' => $id2, 'type' => 'welcome',  'title' => 'Bienvenue', 'time' => null,
             'duration_minutes' => null, 'description' => null, 'notes_officiant' => null],
        ],
    ]);

    // Réordonner : id2 en premier, id1 en second
    $response = $this->actingAs($spouse1)->put(route('ceremony.blocks.update'), [
        'blocks' => [
            ['id' => $id2, 'type' => 'welcome',  'title' => 'Bienvenue', 'time' => null,
             'duration_minutes' => null, 'description' => null],
            ['id' => $id1, 'type' => 'entrance', 'title' => 'Entrée modifiée', 'time' => '14h30',
             'duration_minutes' => 10, 'description' => 'Desc.'],
        ],
    ]);

    $response->assertRedirect();
    $ceremony->refresh();
    expect($ceremony->program[0]['id'])->toBe($id2);
    expect($ceremony->program[1]['title'])->toBe('Entrée modifiée');
});

test('PUT /ceremonie/blocks — les notes_officiant sont préservées lors du réordonnancement', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();

    $blockId = \Illuminate\Support\Str::uuid()->toString();
    $ceremony->update([
        'program' => [
            ['id' => $blockId, 'type' => 'vows', 'title' => 'Vœux', 'time' => null,
             'duration_minutes' => null, 'description' => null, 'notes_officiant' => 'note existante'],
        ],
    ]);

    // Soumettre le bloc sans le champ notes_officiant
    $response = $this->actingAs($spouse1)->put(route('ceremony.blocks.update'), [
        'blocks' => [
            ['id' => $blockId, 'type' => 'vows', 'title' => 'Vœux', 'time' => null,
             'duration_minutes' => null, 'description' => null],
        ],
    ]);

    $response->assertRedirect();
    $ceremony->refresh();
    expect($ceremony->program[0]['notes_officiant'])->toBe('note existante');
});

test('un tiers ne peut pas supprimer un bloc (403)', function () {
    [$spouse1, $spouse2, $ceremony] = coupleWithCeremony();
    $outsider = User::factory()->create();

    $blockId = \Illuminate\Support\Str::uuid()->toString();
    $ceremony->update([
        'program' => [
            ['id' => $blockId, 'type' => 'reading', 'title' => 'Lecture', 'time' => null,
             'duration_minutes' => null, 'description' => null, 'notes_officiant' => null],
        ],
    ]);

    $this->actingAs($outsider)->delete(route('ceremony.blocks.remove', $blockId))->assertStatus(403);
});
