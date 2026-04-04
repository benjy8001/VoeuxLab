<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

uses(TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

/*
 * Enregistre une route admin temporaire avant chaque test.
 * On utilise le FQCN du middleware directement (plutôt que l'alias 'admin')
 * car les alias enregistrés via bootstrap/app.php ne sont pas propagés
 * au routeur lorsqu'une route est définie dynamiquement après le boot en contexte de test.
 */
beforeEach(function () {
    Route::get('/test-admin-route', fn () => 'ok')
        ->middleware(['web', 'auth', EnsureUserIsAdmin::class]);
});

test("un utilisateur non authentifié sur une route admin est redirigé vers login", function () {
    $this->get('/test-admin-route')
        ->assertRedirect(route('login'));
});

test("un utilisateur avec role spouse sur une route admin reçoit 403", function () {
    $user = User::factory()->create(['role' => 'spouse']);

    $this->actingAs($user)
        ->get('/test-admin-route')
        ->assertForbidden();
});

test("un utilisateur avec role admin sur une route admin reçoit 200", function () {
    $user = User::factory()->create(['role' => 'admin']);

    $this->actingAs($user)
        ->get('/test-admin-route')
        ->assertOk()
        ->assertSee('ok');
});
