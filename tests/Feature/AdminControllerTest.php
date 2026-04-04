<?php

use App\Models\Couple;
use App\Models\OfficiantDraft;
use App\Models\User;
use App\Models\VowsDraft;

// ──────────────────────────────────────────────────
// Accès non authentifié
// ──────────────────────────────────────────────────

test('GET /admin redirige vers login pour un visiteur non authentifié', function () {
    $this->get('/admin')->assertRedirect('/login');
});

// ──────────────────────────────────────────────────
// GET /admin — tableau de bord
// ──────────────────────────────────────────────────

test('GET /admin retourne 403 pour un utilisateur spouse', function () {
    $spouse = User::factory()->create(['role' => 'spouse']);

    $this->actingAs($spouse)
        ->get('/admin')
        ->assertForbidden();
});

test('GET /admin retourne 200 pour un admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk();
});

test('GET /admin retourne les statistiques correctes', function () {
    $admin = User::factory()->admin()->create();

    // Créer quelques données
    $spouse1 = User::factory()->create(['role' => 'spouse']);
    $spouse2 = User::factory()->create(['role' => 'spouse']);

    // Couple complet (avec spouse_2_id)
    $couple = Couple::factory()->create([
        'spouse_1_id' => $spouse1->id,
        'spouse_2_id' => $spouse2->id,
    ]);
    $spouse1->update(['couple_id' => $couple->id]);
    $spouse2->update(['couple_id' => $couple->id]);

    // Couple incomplet
    $spouse3 = User::factory()->create(['role' => 'spouse']);
    $incompletCouple = Couple::factory()->create([
        'spouse_1_id' => $spouse3->id,
        'spouse_2_id' => null,
    ]);
    $spouse3->update(['couple_id' => $incompletCouple->id]);

    // VowsDraft complété — création directe pour éviter les effets de bord de la factory
    VowsDraft::create([
        'user_id'      => $spouse1->id,
        'couple_id'    => $couple->id,
        'status'       => 'completed',
        'current_step' => 14,
    ]);

    // VowsDraft non complété
    VowsDraft::create([
        'user_id'      => $spouse2->id,
        'couple_id'    => $couple->id,
        'status'       => 'draft',
        'current_step' => 3,
    ]);

    // OfficiantDraft — création directe pour éviter les effets de bord de la factory
    OfficiantDraft::create([
        'couple_id'    => $couple->id,
        'user_id'      => $admin->id,
        'status'       => 'in_progress',
        'current_step' => 1,
    ]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard', false)
            ->where('stats.total_users', 4)
            ->where('stats.total_couples', 2)
            ->where('stats.full_couples', 1)
            ->where('stats.vows_completed', 1)
            ->where('stats.officiants', 1)
        );
});

// ──────────────────────────────────────────────────
// GET /admin/couples
// ──────────────────────────────────────────────────

test('GET /admin/couples retourne 403 pour un spouse', function () {
    $spouse = User::factory()->create(['role' => 'spouse']);

    $this->actingAs($spouse)
        ->get('/admin/couples')
        ->assertForbidden();
});

test('GET /admin/couples retourne 200 avec les couples pour un admin', function () {
    $admin = User::factory()->admin()->create();

    $spouse1 = User::factory()->create(['role' => 'spouse']);
    $spouse2 = User::factory()->create(['role' => 'spouse']);
    $couple = Couple::factory()->create([
        'spouse_1_id' => $spouse1->id,
        'spouse_2_id' => $spouse2->id,
    ]);

    $this->actingAs($admin)
        ->get('/admin/couples')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Couples', false)
            ->has('couples', 1)
            ->where('couples.0.id', $couple->id)
        );
});

// ──────────────────────────────────────────────────
// GET /admin/users
// ──────────────────────────────────────────────────

test('GET /admin/users retourne 403 pour un spouse', function () {
    $spouse = User::factory()->create(['role' => 'spouse']);

    $this->actingAs($spouse)
        ->get('/admin/users')
        ->assertForbidden();
});

test('GET /admin/users retourne 200 avec la liste des utilisateurs pour un admin', function () {
    $admin = User::factory()->admin()->create();
    $spouse = User::factory()->create(['role' => 'spouse']);

    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Users', false)
            ->has('users', 2)
        );
});

// ──────────────────────────────────────────────────
// PATCH /admin/users/{user}/disable
// ──────────────────────────────────────────────────

test("PATCH /admin/users/{user}/disable retourne 403 si l'admin tente de se désactiver lui-même", function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->patch("/admin/users/{$admin->id}/disable")
        ->assertForbidden();
});

test("PATCH /admin/users/{user}/disable désactive un autre utilisateur en mettant son rôle à 'disabled'", function () {
    $admin = User::factory()->admin()->create();
    $spouse = User::factory()->create(['role' => 'spouse']);

    $this->actingAs($admin)
        ->patch("/admin/users/{$spouse->id}/disable")
        ->assertRedirect();

    expect($spouse->fresh()->role)->toBe('disabled');
});

// ──────────────────────────────────────────────────
// DELETE /admin/users/{user}
// ──────────────────────────────────────────────────

test("DELETE /admin/users/{user} retourne 403 si l'admin tente de supprimer son propre compte", function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->delete("/admin/users/{$admin->id}")
        ->assertForbidden();
});

test('DELETE /admin/users/{user} supprime un autre utilisateur et redirige', function () {
    $admin = User::factory()->admin()->create();
    $spouse = User::factory()->create(['role' => 'spouse']);

    $this->actingAs($admin)
        ->delete("/admin/users/{$spouse->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('users', ['id' => $spouse->id]);
});

// ──────────────────────────────────────────────────
// Middleware admin — accès refusé pour un spouse
// ──────────────────────────────────────────────────

test('un spouse ne peut pas désactiver un autre utilisateur', function () {
    $spouse = User::factory()->create();
    $other = User::factory()->create();
    $this->actingAs($spouse)
        ->patch(route('admin.users.disable', $other))
        ->assertForbidden();
});

test('un spouse ne peut pas supprimer un autre utilisateur', function () {
    $spouse = User::factory()->create();
    $other = User::factory()->create();
    $this->actingAs($spouse)
        ->delete(route('admin.users.destroy', $other))
        ->assertForbidden();
});
