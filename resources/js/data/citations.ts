export interface Citation {
    id: number;
    text: string;
    author: string;
}

export interface CitationCategory {
    key: string;
    label: string;
    icon: string;
    citations: Citation[];
}

export const CITATION_CATEGORIES: CitationCategory[] = [
    {
        key: 'poetry',
        label: 'Poésie',
        icon: '✦',
        citations: [
            { id: 1, text: 'Aimer, c\'est ne pas savoir dire pourquoi on aime.', author: 'Victor Hugo' },
            { id: 2, text: 'Tu es ma nuit et tu es mon étoile.', author: 'Alphonse de Lamartine' },
            { id: 3, text: 'Je t\'aime non seulement pour ce que tu es, mais pour ce que je suis quand je suis avec toi.', author: 'Roy Croft' },
            { id: 4, text: 'L\'amour est la poésie des sens.', author: 'Honoré de Balzac' },
            { id: 5, text: 'Où tu iras, j\'irai. Où tu mourras, je mourrai.', author: 'Livre de Ruth' },
            { id: 6, text: 'Je voudrais que tu sois toujours devant moi, afin que je puisse toujours te voir.', author: 'Paul Éluard' },
        ],
    },
    {
        key: 'humor',
        label: 'Humour',
        icon: '◡',
        citations: [
            { id: 7, text: 'Le mariage est une longue conversation ponctuée de disputes.', author: 'Robert Louis Stevenson' },
            { id: 8, text: 'Se marier, c\'est se mettre à deux pour trouver qu\'on est seul.', author: 'Alexandre Dumas fils' },
            { id: 9, text: 'Un mariage heureux est une longue conversation qui semble toujours trop courte.', author: 'André Maurois' },
            { id: 10, text: 'J\'aime être marié. C\'est très bien d\'avoir quelqu\'un à qui reprocher quelque chose.', author: 'Woody Allen' },
            { id: 11, text: 'Le mariage est le seul voyage organisé où l\'on ne sait jamais où l\'on va.', author: 'Sacha Guitry' },
        ],
    },
    {
        key: 'cinema',
        label: 'Cinéma',
        icon: '◈',
        citations: [
            { id: 12, text: 'Tu me complètes.', author: 'Jerry Maguire (1996)' },
            { id: 13, text: 'Je n\'ai jamais prétendu être un saint.', author: 'Casablanca (1942)' },
            { id: 14, text: 'Je suis à toi. Entièrement et pour toujours.', author: 'Orgueil et Préjugés (2005)' },
            { id: 15, text: 'Si tu sautes, je saute.', author: 'Titanic (1997)' },
            { id: 16, text: 'Tu es celle que je veux pour le reste de ma vie.', author: 'Grease (1978)' },
        ],
    },
    {
        key: 'music',
        label: 'Chanson',
        icon: '♪',
        citations: [
            { id: 17, text: 'La vie en rose. Les yeux qui font baisser les miens.', author: 'Édith Piaf' },
            { id: 18, text: 'Ce soir, je ne suis plus seul. Tu viens de me donner le soleil.', author: 'Francis Cabrel' },
            { id: 19, text: 'Et si tu n\'existais pas, dis-moi pourquoi j\'existerais.', author: 'Joe Dassin' },
            { id: 20, text: 'On a toute la vie pour se faire des adieux.', author: 'Barbara' },
            { id: 21, text: 'Je t\'aimais, je t\'aime et je t\'aimerai.', author: 'Francis Cabrel' },
        ],
    },
];
