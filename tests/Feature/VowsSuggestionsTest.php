<?php

use App\Models\User;

test('retourne des suggestions pour une combinaison valide', function () {
    $user = userWithDraft();

    $this->actingAs($user)
        ->getJson(route('voeux.suggestions', [
            'question_key' => 'meeting_story',
            'tone'         => 'balanced',
        ]))
        ->assertOk()
        ->assertJsonStructure(['suggestions'])
        ->assertJson(fn ($json) => $json
            ->has('suggestions')
            ->whereType('suggestions', 'array')
        );
});

test('retourne un tableau pour une combinaison valide quelconque', function () {
    $user = userWithDraft();

    $response = $this->actingAs($user)
        ->getJson(route('voeux.suggestions', [
            'question_key' => 'inspiration',
            'tone'         => 'light',
        ]))
        ->assertOk()
        ->assertJsonStructure(['suggestions']);

    expect($response->json('suggestions'))->toBeArray();
});

test('retourne 422 si question_key est invalide', function () {
    $user = userWithDraft();

    $this->actingAs($user)
        ->getJson(route('voeux.suggestions', [
            'question_key' => 'invalid_key',
            'tone'         => 'balanced',
        ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['question_key']);
});

test('retourne 422 si tone est invalide', function () {
    $user = userWithDraft();

    $this->actingAs($user)
        ->getJson(route('voeux.suggestions', [
            'question_key' => 'meeting_story',
            'tone'         => 'humorous',
        ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['tone']);
});

test('la route suggestions est protégée par auth', function () {
    $this->getJson(route('voeux.suggestions', [
        'question_key' => 'meeting_story',
        'tone'         => 'balanced',
    ]))
        ->assertStatus(401);
});
