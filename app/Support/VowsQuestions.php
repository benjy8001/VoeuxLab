<?php

namespace App\Support;

readonly class VowsQuestions
{
    public const array QUESTIONS = [
        ['key' => 'meeting_story',      'order' => 1,  'label' => 'Comment vous êtes-vous rencontrés ?',                           'placeholder' => 'Racontez les circonstances de votre rencontre...', 'hint' => 'Où étiez-vous ? La circonstance ?'],
        ['key' => 'first_memory',       'order' => 2,  'label' => 'Quel est votre premier souvenir marquant ensemble ?',            'placeholder' => 'Ce moment gravé dans votre mémoire...', 'hint' => ''],
        ['key' => 'falling_in_love',    'order' => 3,  'label' => 'À quel moment avez-vous su que c\'était lui/elle ?',             'placeholder' => 'Le déclic, la certitude...', 'hint' => ''],
        ['key' => 'admired_quality',    'order' => 4,  'label' => 'Quelle qualité admirez-vous le plus chez votre partenaire ?',    'placeholder' => 'Ce qui vous touche profondément...', 'hint' => ''],
        ['key' => 'overcome_together',  'order' => 5,  'label' => 'Quel défi avez-vous relevé ensemble ?',                         'placeholder' => 'Un moment difficile qui vous a soudés...', 'hint' => ''],
        ['key' => 'laughter',           'order' => 6,  'label' => 'Qu\'est-ce qui vous fait rire ensemble ?',                      'placeholder' => 'Vos moments de complicité...', 'hint' => 'Une note légère pour la cérémonie'],
        ['key' => 'dreams_support',     'order' => 7,  'label' => 'Comment vous soutient-il/elle dans vos rêves ?',                'placeholder' => 'La façon dont il/elle vous encourage...', 'hint' => ''],
        ['key' => 'shared_dream',       'order' => 8,  'label' => 'Quel est votre rêve commun pour l\'avenir ?',                   'placeholder' => 'Ce que vous voulez construire ensemble...', 'hint' => ''],
        ['key' => 'self_discovery',     'order' => 9,  'label' => 'Qu\'avez-vous appris sur vous-même grâce à votre partenaire ?', 'placeholder' => 'La version de vous-même qu\'il/elle révèle...', 'hint' => ''],
        ['key' => 'main_promise',       'order' => 10, 'label' => 'Quelle est votre promesse principale ?',                        'placeholder' => 'Ce que vous vous engagez à être pour lui/elle...', 'hint' => ''],
        ['key' => 'hard_times_promise', 'order' => 11, 'label' => 'Dans les moments difficiles, que promettez-vous ?',             'placeholder' => 'Votre engagement dans l\'adversité...', 'hint' => ''],
        ['key' => 'growing_together',   'order' => 12, 'label' => 'Comment souhaitez-vous grandir ensemble ?',                     'placeholder' => 'Vos valeurs, votre façon de traverser la vie...', 'hint' => ''],
        ['key' => 'inspiration',        'order' => 13, 'label' => 'Une citation ou un poème qui vous inspire ?',                   'placeholder' => '(optionnel) Un texte qui vous ressemble...', 'hint' => 'Optionnel'],
        ['key' => 'closing_words',      'order' => 14, 'label' => 'Comment souhaitez-vous terminer vos vœux ?',                   'placeholder' => 'Vos derniers mots avant le oui...', 'hint' => ''],
    ];

    public static function all(): array
    {
        return self::QUESTIONS;
    }

    public static function find(string $key): ?array
    {
        return collect(self::QUESTIONS)->firstWhere('key', $key);
    }

    public static function count(): int
    {
        return count(self::QUESTIONS);
    }

    public static function validKeys(): array
    {
        return array_column(self::QUESTIONS, 'key');
    }
}
