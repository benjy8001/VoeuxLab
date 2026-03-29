<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

use App\Models\Couple;
use App\Models\User;
use App\Models\VowsAnswer;
use App\Models\VowsDraft;
use App\Services\VowsGeneratorService;

function draftWithAnswers(array $answers): VowsDraft
{
    $user = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $user->id]);
    $draft = VowsDraft::factory()->create(['user_id' => $user->id, 'couple_id' => $couple->id]);

    $order = 1;
    foreach ($answers as $key => $text) {
        VowsAnswer::factory()->create([
            'vows_draft_id' => $draft->id,
            'question_key'  => $key,
            'answer_text'   => $text,
            'step_order'    => $order++,
        ]);
    }

    return $draft->load('answers');
}

test('generator assembles text from answers', function () {
    $draft = draftWithAnswers([
        'meeting_story' => 'Nous nous sommes rencontrés à Paris.',
        'main_promise'  => 'être là pour toi chaque jour',
    ]);

    $text = (new VowsGeneratorService())->generate($draft);

    expect($text)->toContain('Nous nous sommes rencontrés à Paris.');
    expect($text)->toContain('Je te promets : être là pour toi chaque jour');
});

test('generator wraps inspiration in guillemets', function () {
    $draft = draftWithAnswers(['inspiration' => "L'amour est la seule victoire."]);

    $text = (new VowsGeneratorService())->generate($draft);

    expect($text)->toContain("« L'amour est la seule victoire. »");
});

test('generator prefixes admired_quality', function () {
    $draft = draftWithAnswers(['admired_quality' => 'ta générosité infinie']);

    $text = (new VowsGeneratorService())->generate($draft);

    expect($text)->toContain("Ce que j'admire le plus en toi : ta générosité infinie");
});

test('generator skips empty answers', function () {
    $draft = draftWithAnswers(['meeting_story' => 'À Paris.']);

    $text = (new VowsGeneratorService())->generate($draft);

    expect($text)->not->toContain('Je te promets :');
    expect($text)->not->toContain('« »');
    expect($text)->not->toContain("Ce que j'admire");
});

test('generator returns empty string for draft with no answers', function () {
    $user = User::factory()->create();
    $couple = Couple::factory()->create(['spouse_1_id' => $user->id]);
    $draft = VowsDraft::factory()->create(['user_id' => $user->id, 'couple_id' => $couple->id]);
    $draft->load('answers');

    $text = (new VowsGeneratorService())->generate($draft);

    expect($text)->toBe('');
});
