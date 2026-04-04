<?php

namespace App\Services;

class VowsSuggestionsService
{
    public function get(string $questionKey, string $tone): array
    {
        return self::$data[$tone][$questionKey] ?? [];
    }

    private static array $data = [
        'balanced' => [
            'meeting_story' => [
                'Tout a commencé par un hasard qui ressemble aujourd\'hui à une évidence.',
                'Je me souviens encore de la première fois où je t\'ai vu·e — je ne savais pas encore que tout changerait.',
            ],
            'first_memory' => [
                'Notre premier souvenir ensemble, c\'est quelque chose que je n\'aurais jamais pu inventer.',
                'Il y a des moments qu\'on ne choisit pas, mais qu\'on garde toute sa vie.',
            ],
            'falling_in_love' => [
                'Je ne saurais pas dire exactement quand, mais je sais que c\'est toi.',
                'À un moment précis, tout est devenu clair — et ce moment, c\'est toi.',
            ],
            'admired_quality' => [
                'Ce que j\'admire en toi, c\'est quelque chose que les mots peinent à saisir.',
                'Tu as cette façon d\'être qui m\'apprend quelque chose sur moi chaque jour.',
            ],
            'overcome_together' => [
                'On a traversé des moments difficiles, et ça n\'a fait que nous rapprocher.',
                'Face à l\'adversité, j\'ai découvert à quel point on est forts ensemble.',
            ],
            'laughter' => [
                'Ce qui me fait rire avec toi, c\'est notre façon unique de voir le monde.',
                'Notre complicité, c\'est peut-être notre plus grand trésor.',
            ],
            'dreams_support' => [
                'Tu crois en mes rêves parfois plus que moi.',
                'Tu m\'as appris que mes ambitions méritent d\'être portées.',
            ],
            'shared_dream' => [
                'Ce que nous voulons construire ensemble, c\'est quelque chose de grand et de simple à la fois.',
                'Notre avenir commun prend forme dans les petites décisions du quotidien.',
            ],
            'self_discovery' => [
                'À tes côtés, je découvre chaque jour une version de moi que je ne connaissais pas.',
                'Tu m\'as révélé des forces et des vulnérabilités que je ne soupçonnais pas.',
            ],
            'main_promise' => [
                'Aujourd\'hui, devant ceux qui nous sont chers, je te promets…',
                'Ma promesse la plus sincère, celle que je veux tenir chaque jour, c\'est…',
            ],
            'hard_times_promise' => [
                'Dans les moments difficiles, je te promets de rester.',
                'Quand la vie sera dure, je veux être celui/celle qui reste à tes côtés.',
            ],
            'growing_together' => [
                'Je veux qu\'on grandisse ensemble, pas chacun de son côté.',
                'Notre façon de traverser la vie ensemble, c\'est aussi ce que je veux construire.',
            ],
            'inspiration' => [
                'Il y a ces mots qui semblent avoir été écrits pour nous.',
                'J\'ai trouvé dans cette phrase quelque chose qui ressemble à ce qu\'on vit.',
            ],
            'closing_words' => [
                'Je t\'aime, et je veux que ce soit le fil conducteur de toute notre vie.',
                'Ces deux mots — oui, je le veux — résument tout ce que j\'ai à te dire.',
            ],
        ],

        'moving' => [
            'meeting_story' => [
                'Notre histoire a commencé dans l\'ordinaire, et c\'est là que se cache le miracle.',
                'Avant toi, je ne savais pas que certaines rencontres changent tout.',
            ],
            'first_memory' => [
                'Ce souvenir-là, je le porte comme quelque chose de précieux et d\'irremplaçable.',
                'Je n\'oublierai jamais ce moment où j\'ai compris que tout était différent avec toi.',
            ],
            'falling_in_love' => [
                'Ce n\'est pas un coup de foudre que j\'ai ressenti — c\'est une évidence qui s\'est imposée doucement.',
                'À un moment silencieux, quelque chose en moi a su que c\'était toi.',
            ],
            'admired_quality' => [
                'Tu m\'as appris à voir le monde avec d\'autres yeux.',
                'Il y a en toi une lumière que je n\'ai trouvée nulle part ailleurs.',
            ],
            'overcome_together' => [
                'On s\'est tenus l\'un à l\'autre dans des moments où tout aurait pu s\'effondrer.',
                'Cette épreuve nous a montré ce que nous sommes vraiment l\'un pour l\'autre.',
            ],
            'laughter' => [
                'Même dans les jours sombres, tu sais trouver en moi un sourire que je croyais perdu.',
                'Notre rire ensemble, c\'est une forme de résistance.',
            ],
            'dreams_support' => [
                'Tu as été la première personne à croire vraiment en ce que je portais.',
                'Sans toi, certains de mes rêves seraient restés des rêves.',
            ],
            'shared_dream' => [
                'Ce que nous voulons construire n\'est pas juste un projet — c\'est une promesse faite à la vie.',
                'Notre rêve commun, c\'est de continuer à nous choisir, chaque matin.',
            ],
            'self_discovery' => [
                'Avec toi, j\'ai découvert des parts de moi que je n\'aurais jamais rencontrées seul·e.',
                'Tu es le miroir dans lequel je reconnais enfin qui je suis vraiment.',
            ],
            'main_promise' => [
                'La promesse que je te fais aujourd\'hui, je veux qu\'elle résonne longtemps après ce jour.',
                'Ce que je m\'engage à t\'offrir, c\'est tout ce que j\'ai de plus vrai.',
            ],
            'hard_times_promise' => [
                'Dans les jours où tout sera lourd, je veux être ton ancre.',
                'Quand la nuit sera longue, je promets de rester et de chercher l\'aube avec toi.',
            ],
            'growing_together' => [
                'Grandir ensemble, c\'est accepter de changer sans jamais se perdre.',
                'Je veux qu\'on vieillisse avec la même curiosité qu\'aujourd\'hui.',
            ],
            'inspiration' => [
                'Ces mots-là m\'ont accompagné jusqu\'à toi.',
                'J\'ai trouvé dans cette phrase quelque chose qui ressemble à ce que tu représentes pour moi.',
            ],
            'closing_words' => [
                'Je t\'aime — et c\'est la vérité la plus simple et la plus profonde que je connaisse.',
                'Ce oui que je prononce, c\'est tout mon cœur qui parle.',
            ],
        ],

        'light' => [
            'meeting_story' => [
                'Pour être honnête, ma première impression n\'était pas vraiment celle que je raconte en société.',
                'On dit que les grandes histoires commencent par un coup de foudre. La nôtre a plutôt commencé par…',
            ],
            'first_memory' => [
                'Notre premier vrai souvenir ensemble ? Disons que ce n\'était pas exactement prévu au programme.',
                'Ça n\'avait rien d\'un film romantique — et c\'est exactement pour ça que je m\'en souviens.',
            ],
            'falling_in_love' => [
                'J\'ai su que c\'était toi à peu près au moment où j\'ai arrêté d\'essayer de m\'en convaincre.',
                'Ce n\'était pas un coup de foudre. C\'était plutôt une évidence qui s\'est imposée malgré moi.',
            ],
            'admired_quality' => [
                'Ce que j\'admire chez toi ? Ce petit truc agaçant qui s\'est transformé en quelque chose que j\'adore.',
                'Tu as un talent particulier — celui de me surprendre alors que je croyais tout savoir.',
            ],
            'overcome_together' => [
                'On a failli se planter. On s\'en est sortis. C\'est ça, une équipe.',
                'Quelqu\'un de sensé nous aurait peut-être conseillé de ne pas essayer. On a quand même essayé.',
            ],
            'laughter' => [
                'On rit pour des raisons qu\'on serait incapables d\'expliquer à quelqu\'un d\'autre.',
                'Notre sens de l\'humour commun est notre super-pouvoir secret.',
            ],
            'dreams_support' => [
                'Tu m\'encourages dans mes projets, même les plus improbables — et c\'est une des choses que j\'aime le plus chez toi.',
                'Tu ne ris jamais de mes idées. Enfin, presque jamais.',
            ],
            'shared_dream' => [
                'Notre plan pour l\'avenir ? Il existe, même s\'il change régulièrement.',
                'On s\'est mis d\'accord sur l\'essentiel, et pour le reste on improvise plutôt bien.',
            ],
            'self_discovery' => [
                'Depuis que je te connais, j\'ai découvert des côtés de moi que je n\'avais pas commandés.',
                'Tu as réussi à me faire apprécier des choses que je détestais. Je ne sais pas encore si c\'est bien.',
            ],
            'main_promise' => [
                'Ma promesse la plus honnête — celle que je peux tenir sans croiser les doigts dans le dos — c\'est…',
                'Je te promets de faire de mon mieux. C\'est vague, mais c\'est sincère.',
            ],
            'hard_times_promise' => [
                'Quand ça sera dur, je promets de rester. Même si je râle un peu.',
                'Dans les moments difficiles, je promets de ne pas disparaître — et d\'apporter du chocolat si nécessaire.',
            ],
            'growing_together' => [
                'Grandir ensemble, pour moi, c\'est continuer à se surprendre sans se perdre.',
                'Je veux qu\'on continue à rire des mêmes choses dans vingt ans.',
            ],
            'inspiration' => [
                'J\'ai trouvé cette phrase et j\'ai pensé très fort à toi.',
                'Ces mots-là n\'auraient pas eu de sens avant que tu existes dans ma vie.',
            ],
            'closing_words' => [
                'Je t\'aime — et maintenant que c\'est officiel, je n\'aurai plus à le répéter autant. (Si.)',
                'Mon dernier mot avant le oui ? Te voir sourire en ce moment me dit que c\'était le bon.',
            ],
        ],

        'poetic' => [
            'meeting_story' => [
                'Il y a des rencontres qui ressemblent à des commencements de monde.',
                'Tu es arrivé·e dans ma vie comme une lumière qu\'on n\'attendait plus.',
            ],
            'first_memory' => [
                'Ce souvenir-là flotte encore entre nous, précis et doux comme une première neige.',
                'Je le porte en moi comme une image fixe — le début de quelque chose d\'immense.',
            ],
            'falling_in_love' => [
                'Ce n\'est pas un instant que je peux nommer — c\'est une saison entière qui a changé de couleur.',
                'J\'ai su que c\'était toi comme on sait qu\'il va faire beau : longtemps avant de voir le ciel.',
            ],
            'admired_quality' => [
                'Tu portes en toi une lumière que je ne me lasse pas de regarder.',
                'Il y a en toi quelque chose de rare — comme une couleur que les autres ne voient pas.',
            ],
            'overcome_together' => [
                'On a traversé des eaux sombres, et on en est sortis avec les mains liées.',
                'Cette épreuve nous a révélés l\'un à l\'autre comme la mer révèle les rochers à marée basse.',
            ],
            'laughter' => [
                'Notre rire ensemble ressemble à une langue que nous serions les seuls à parler.',
                'Il y a des complicités qui ressemblent à des pays entiers — on y habite et on n\'en sort pas.',
            ],
            'dreams_support' => [
                'Tu souffles sur mes rêves pour qu\'ils ne s\'éteignent pas.',
                'Grâce à toi, j\'ai appris à ne pas avoir honte de ce que je désire.',
            ],
            'shared_dream' => [
                'L\'horizon que nous construisons n\'appartient qu\'à nous.',
                'Notre rêve commun est une promesse que nous faisons à l\'avenir.',
            ],
            'self_discovery' => [
                'À tes côtés, je suis devenu·e quelqu\'un que je suis fier·e de rencontrer chaque matin.',
                'Tu m\'as appris que certaines parts de soi ne s\'ouvrent qu\'à la lumière d\'un autre.',
            ],
            'main_promise' => [
                'Ma promesse, c\'est une rivière — discrète, constante, qui ne s\'arrête pas.',
                'Ce que je te promets aujourd\'hui, je le porterai comme on porte un horizon.',
            ],
            'hard_times_promise' => [
                'Dans les nuits longues, je veux être ta boussole.',
                'Quand le chemin sera étroit, je promets de ne pas lâcher ta main.',
            ],
            'growing_together' => [
                'Vieillir ensemble, c\'est devenir lentement le paysage de l\'autre.',
                'Je veux qu\'on laisse le temps faire son œuvre sans jamais en avoir peur.',
            ],
            'inspiration' => [
                'Ce poème me ressemblait avant même que je sache pourquoi — et puis tu es arrivé·e.',
                'Dans ces mots, j\'ai reconnu quelque chose de nous que je ne savais pas encore nommer.',
            ],
            'closing_words' => [
                'Mon oui est une porte que j\'ouvre — et derrière, c\'est toute une vie qui nous attend.',
                'Ces mots, je les prononce comme on allume un feu — pour longtemps, pour nous.',
            ],
        ],
    ];
}
