<?php

namespace App\Support;

readonly class VowsQuestions
{
    public const array QUESTIONS = [
        ['key' => 'meeting_story',      'order' => 1,  'label' => 'Comment vous êtes-vous rencontrés ?',                           'placeholder' => 'Racontez les circonstances de votre rencontre...', 'hint' => ''],
        ['key' => 'first_memory',       'order' => 2,  'label' => 'Quel est votre premier souvenir marquant ensemble ?',            'placeholder' => 'Ce moment gravé dans votre mémoire...', 'hint' => ''],
        ['key' => 'falling_in_love',    'order' => 3,  'label' => 'À quel moment avez-vous su que c\'était lui/elle ?',             'placeholder' => 'Le déclic, la certitude...', 'hint' => ''],
        ['key' => 'admired_quality',    'order' => 4,  'label' => 'Quelle qualité admirez-vous le plus chez votre partenaire ?',    'placeholder' => 'Ce qui vous touche profondément...', 'hint' => ''],
        ['key' => 'overcome_together',  'order' => 5,  'label' => 'Quel défi avez-vous relevé ensemble ?',                         'placeholder' => 'Un moment difficile qui vous a soudés...', 'hint' => ''],
        ['key' => 'laughter',           'order' => 6,  'label' => 'Qu\'est-ce qui vous fait rire ensemble ?',                      'placeholder' => 'Vos moments de complicité...', 'hint' => ''],
        ['key' => 'dreams_support',     'order' => 7,  'label' => 'Comment vous soutient-il/elle dans vos rêves ?',                'placeholder' => 'La façon dont il/elle vous encourage...', 'hint' => ''],
        ['key' => 'shared_dream',       'order' => 8,  'label' => 'Quel est votre rêve commun pour l\'avenir ?',                   'placeholder' => 'Ce que vous voulez construire ensemble...', 'hint' => ''],
        ['key' => 'self_discovery',     'order' => 9,  'label' => 'Qu\'avez-vous appris sur vous-même grâce à votre partenaire ?', 'placeholder' => 'La version de vous-même qu\'il/elle révèle...', 'hint' => ''],
        ['key' => 'main_promise',       'order' => 10, 'label' => 'Quelle est votre promesse principale ?',                        'placeholder' => 'Ce que vous vous engagez à être pour lui/elle...', 'hint' => ''],
        ['key' => 'hard_times_promise', 'order' => 11, 'label' => 'Dans les moments difficiles, que promettez-vous ?',             'placeholder' => 'Votre engagement dans l\'adversité...', 'hint' => ''],
        ['key' => 'growing_together',   'order' => 12, 'label' => 'Comment souhaitez-vous grandir ensemble ?',                     'placeholder' => 'Vos valeurs, votre façon de traverser la vie...', 'hint' => ''],
        ['key' => 'inspiration',        'order' => 13, 'label' => 'Une citation ou un poème qui vous inspire ?',                   'placeholder' => '(optionnel) Un texte qui vous ressemble...', 'hint' => 'Optionnel'],
        ['key' => 'closing_words',      'order' => 14, 'label' => 'Comment souhaitez-vous terminer vos vœux ?',                   'placeholder' => 'Vos derniers mots avant le oui...', 'hint' => ''],
    ];

    private const array VARIANTS = [
        'moving' => [
            'admired_quality'    => ['label' => 'Comment a-t-il/elle transformé votre vision du monde ?',          'placeholder' => 'Ce que vous ne voyiez pas avant lui/elle...'],
            'overcome_together'  => ['label' => 'Dans quel moment de fragilité vous êtes-vous le plus soutenus ?', 'placeholder' => 'Ce que cette épreuve vous a révélé l\'un sur l\'autre...'],
            'self_discovery'     => ['label' => 'Quelle part de vous-même n\'existait pas avant cette relation ?', 'placeholder' => 'La personne que vous devenez à ses côtés...'],
            'main_promise'       => ['label' => 'Quelle promesse portez-vous dans votre cœur depuis le début ?',  'placeholder' => 'Votre engagement le plus profond...'],
            'closing_words'      => ['label' => 'Si vous ne deviez garder qu\'une phrase, quelle serait-elle ?',  'placeholder' => 'Ces mots qui résument tout...'],
        ],
        'light' => [
            'meeting_story'      => ['label' => 'Comment s\'est vraiment passée votre rencontre (version honnête) ?',               'placeholder' => 'Sans fard, avec humour si besoin...'],
            'admired_quality'    => ['label' => 'Quel défaut de votre partenaire avez-vous appris à aimer ?',                       'placeholder' => 'Ce petit travers qui vous fait sourire maintenant...'],
            'laughter'           => ['label' => 'Votre moment le plus embarrassant ensemble ?',                                      'placeholder' => 'Celui dont vous riez encore...'],
            'overcome_together'  => ['label' => 'Le moment où vous avez failli tout faire rater (et comment vous vous en êtes sortis) ?', 'placeholder' => 'L\'anecdote qui résume votre équipe...'],
            'main_promise'       => ['label' => 'Votre promesse la plus sincère (et la plus réaliste) ?',                           'placeholder' => 'Ce que vous pouvez honnêtement promettre...'],
            'closing_words'      => ['label' => 'Votre phrase finale — avec tout ce que vous avez de vrai en vous ?',               'placeholder' => 'Drôle, tendre, ou les deux à la fois...'],
        ],
        'poetic' => [
            'meeting_story'   => ['label' => 'Si votre rencontre était une scène d\'un roman, comment la décririez-vous ?', 'placeholder' => 'Une image, une couleur, une sensation...'],
            'admired_quality' => ['label' => 'Quelle lumière votre partenaire apporte-t-il/elle dans votre vie ?',          'placeholder' => 'Une métaphore, une image poétique...'],
            'falling_in_love' => ['label' => 'À quel paysage ressemble votre amour ?',                                      'placeholder' => 'Montagne, mer, forêt la nuit... laissez venir l\'image...'],
            'shared_dream'    => ['label' => 'Quel horizon construisez-vous ensemble ?',                                    'placeholder' => 'En images, en couleurs, en sensations...'],
            'main_promise'    => ['label' => 'Quelle promesse porteriez-vous gravée sur une pierre ?',                     'placeholder' => 'Simple, essentielle, éternelle...'],
            'closing_words'   => ['label' => 'Les derniers mots, ceux qui resteront dans l\'air après le oui ?',           'placeholder' => 'Laissez venir la poésie naturellement...'],
        ],
    ];

    public static function forTone(string $tone): array
    {
        if ($tone === 'balanced' || !isset(self::VARIANTS[$tone])) {
            return self::QUESTIONS;
        }

        $variants = self::VARIANTS[$tone];

        return array_map(function (array $q) use ($variants): array {
            if (isset($variants[$q['key']])) {
                return array_merge($q, $variants[$q['key']]);
            }
            return $q;
        }, self::QUESTIONS);
    }

    public static function all(): array       { return self::QUESTIONS; }
    public static function find(string $key): ?array { return collect(self::QUESTIONS)->firstWhere('key', $key); }
    public static function count(): int        { return count(self::QUESTIONS); }
    public static function validKeys(): array  { return array_column(self::QUESTIONS, 'key'); }
    public static function validTones(): array { return ['balanced', 'moving', 'light', 'poetic']; }
}
