<?php

namespace App\Services;

use App\Models\OfficiantDraft;

class OfficiantGeneratorService
{
    public function generate(OfficiantDraft $draft): string
    {
        $answers = $draft->answers->keyBy('question_key');
        $get = fn(string $key): string => $answers[$key]->answer_text ?? '';

        $parts = [];

        $this->add($parts, $get('officiant_intro'));
        $this->add($parts, $get('couple_link'));
        $this->add($parts, $get('how_they_met'));
        $this->add($parts, $get('anecdote'));
        $this->add($parts, $get('admiration'));
        $this->add($parts, $get('advice'));

        $rings = $get('ring_exchange');
        if ($rings) {
            $parts[] = "— Échange des alliances —\n\n{$rings}";
        }

        $this->add($parts, $get('conclusion'));

        return implode("\n\n", $parts);
    }

    private function add(array &$parts, string $text): void
    {
        if ($text !== '') {
            $parts[] = $text;
        }
    }
}
