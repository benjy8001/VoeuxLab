<?php

namespace App\Services;

use App\Models\VowsDraft;

class VowsGeneratorService
{
    public function generate(VowsDraft $draft): string
    {
        $answers = $draft->answers->keyBy('question_key');
        $get = fn(string $key): string => $answers[$key]->answer_text ?? '';

        $parts = [];

        $this->add($parts, $get('meeting_story'));
        $this->add($parts, $get('first_memory'));
        $this->add($parts, $get('falling_in_love'));

        $quality = $get('admired_quality');
        if ($quality !== '') {
            $parts[] = "Ce que j'admire le plus en toi : {$quality}";
        }

        $this->add($parts, $get('overcome_together'));
        $this->add($parts, $get('laughter'));
        $this->add($parts, $get('dreams_support'));
        $this->add($parts, $get('shared_dream'));
        $this->add($parts, $get('self_discovery'));

        $promise = $get('main_promise');
        if ($promise !== '') {
            $parts[] = "Je te promets : {$promise}";
        }

        $this->add($parts, $get('hard_times_promise'));
        $this->add($parts, $get('growing_together'));

        $inspiration = $get('inspiration');
        if ($inspiration !== '') {
            $parts[] = "« {$inspiration} »";
        }

        $this->add($parts, $get('closing_words'));

        return implode("\n\n", $parts);
    }

    private function add(array &$parts, string $text): void
    {
        if ($text !== '') {
            $parts[] = $text;
        }
    }
}
