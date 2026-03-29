<?php

use App\Models\Couple;
use App\Models\User;
use App\Models\VowsDraft;

test('user can download vows as PDF', function () {
    $user = User::factory()->create(['name' => 'Marie']);
    $couple = Couple::factory()->create(['spouse_1_id' => $user->id]);
    $user->update(['couple_id' => $couple->id]);
    VowsDraft::factory()->create([
        'user_id'        => $user->id,
        'couple_id'      => $couple->id,
        'generated_text' => 'Je te promets de t\'aimer chaque jour.',
        'status'         => 'completed',
    ]);

    $response = $this->actingAs($user)->get(route('voeux.export'));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
});

test('user without generated text gets 404', function () {
    $user = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $user->id]);
    $user->update(['couple_id' => $couple->id]);
    VowsDraft::factory()->create([
        'user_id'   => $user->id,
        'couple_id' => $couple->id,
    ]);

    $this->actingAs($user)->get(route('voeux.export'))->assertStatus(404);
});

test('unauthenticated user cannot download PDF', function () {
    $this->get(route('voeux.export'))->assertRedirect(route('login'));
});
