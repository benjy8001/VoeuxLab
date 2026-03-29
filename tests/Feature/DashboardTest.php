<?php

use App\Models\Couple;
use App\Models\User;
use App\Models\VowsDraft;

test('authenticated user sees dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->component('Dashboard'));
});

test('dashboard shows couple info when user has couple', function () {
    $user = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $user->id]);
    $user->update(['couple_id' => $couple->id]);
    VowsDraft::factory()->create([
        'user_id'      => $user->id,
        'couple_id'    => $couple->id,
        'current_step' => 7,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('vows_progress')
            ->where('vows_progress.current_step', 7)
            ->has('couple')
            ->where('couple.invitation_code', $couple->invitation_code)
        );
});

test('dashboard shows null couple and vows_progress for new user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('couple', null)
            ->where('vows_progress', null)
        );
});
