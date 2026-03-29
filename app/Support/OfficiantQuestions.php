<?php

namespace App\Support;

readonly class OfficiantQuestions
{
    public const array QUESTIONS = [
        ['key' => 'officiant_intro', 'order' => 1, 'label' => 'Comment vous présentez-vous aux invités ?',              'placeholder' => 'Votre prénom, votre lien avec l\'un ou l\'autre...', 'hint' => 'Soyez bref, vous développerez ensuite.'],
        ['key' => 'couple_link',     'order' => 2, 'label' => 'Quel est votre lien avec le couple ?',                    'placeholder' => 'Ami, famille, collègue... comment vous avez noué ce lien...', 'hint' => ''],
        ['key' => 'how_they_met',    'order' => 3, 'label' => 'Comment le couple s\'est-il rencontré (votre version) ?', 'placeholder' => 'Ce que vous avez observé, entendu ou vécu...', 'hint' => 'Votre point de vue extérieur apporte de la fraîcheur.'],
        ['key' => 'anecdote',        'order' => 4, 'label' => 'Une anecdote qui les illustre parfaitement ?',            'placeholder' => 'Une scène, un moment qui dit tout d\'eux deux...', 'hint' => ''],
        ['key' => 'admiration',      'order' => 5, 'label' => 'Ce que vous admirez le plus en eux ensemble ?',           'placeholder' => 'Ce que leur relation dégage, apporte aux autres...', 'hint' => ''],
        ['key' => 'advice',          'order' => 6, 'label' => 'Votre conseil ou vœu pour leur vie commune ?',            'placeholder' => 'Une sagesse, une invitation à...', 'hint' => 'Court et sincère vaut mieux que long et générique.'],
        ['key' => 'ring_exchange',   'order' => 7, 'label' => 'Votre texte pour accompagner l\'échange des alliances ?', 'placeholder' => 'Les mots qui guideront ce moment...', 'hint' => 'Ce texte sera lu juste avant l\'échange.'],
        ['key' => 'conclusion',      'order' => 8, 'label' => 'Votre conclusion pour les invités ?',                     'placeholder' => 'L\'annonce de leur union, l\'invitation à les applaudir...', 'hint' => ''],
    ];

    public static function all(): array       { return self::QUESTIONS; }
    public static function count(): int        { return count(self::QUESTIONS); }
    public static function validKeys(): array  { return array_column(self::QUESTIONS, 'key'); }
}
