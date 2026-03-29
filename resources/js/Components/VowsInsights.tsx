interface Insight {
    type: 'warning' | 'tip' | 'success';
    message: string;
}

interface Props {
    text: string;
    partnerName: string | null;
}

function analyzeVows(text: string, partnerName: string | null): Insight[] {
    if (!text.trim()) return [];

    const insights: Insight[] = [];
    const wordCount = text.trim().split(/\s+/).length;
    const lower = text.toLowerCase();

    // Longueur
    if (wordCount < 150) {
        insights.push({
            type: 'warning',
            message: `Vos vœux sont courts (${wordCount} mots). Pensez à développer davantage.`,
        });
    } else if (wordCount > 550) {
        insights.push({
            type: 'tip',
            message: `Vos vœux sont longs (${wordCount} mots, ~${Math.round(wordCount / 130)} min). Envisagez de les raccourcir pour une lecture confortable.`,
        });
    }

    // Promesse explicite
    if (
        !lower.includes('je te promets') &&
        !lower.includes('je promets') &&
        !lower.includes("je m'engage")
    ) {
        insights.push({
            type: 'tip',
            message: 'Pas de promesse explicite détectée. Une formulation directe ("Je te promets…") marque les esprits.',
        });
    }

    // Prénom du partenaire
    if (partnerName && !lower.includes(partnerName.toLowerCase())) {
        insights.push({
            type: 'tip',
            message: `Le prénom de ${partnerName} n'apparaît pas dans vos vœux. L'appeler par son prénom crée un moment fort.`,
        });
    }

    // Note légère
    const hasLightNote =
        lower.includes('rire') ||
        lower.includes('sourire') ||
        lower.includes('drôle') ||
        lower.includes('fou rire') ||
        lower.includes('rigol');

    if (!hasLightNote && wordCount > 150) {
        insights.push({
            type: 'tip',
            message: "Votre texte semble entièrement sérieux. Une touche légère peut détendre l'atmosphère lors de la cérémonie.",
        });
    }

    // Tout est bon
    if (insights.length === 0) {
        insights.push({
            type: 'success',
            message: 'Vos vœux sont bien équilibrés. Bon courage pour la répétition !',
        });
    }

    return insights;
}

const ICONS: Record<Insight['type'], string> = {
    warning: '⚠',
    tip: '○',
    success: '✓',
};

const COLORS: Record<Insight['type'], string> = {
    warning: 'text-orange-600 bg-orange-50 border-orange-200',
    tip:     'text-stone-600 bg-stone-50 border-stone-200',
    success: 'text-green-700 bg-green-50 border-green-200',
};

export default function VowsInsights({ text, partnerName }: Props) {
    const insights = analyzeVows(text, partnerName);

    if (insights.length === 0) return null;

    return (
        <div className="mt-6">
            <p className="text-xs text-stone-400 uppercase tracking-widest mb-3">
                Conseils de relecture
            </p>
            <div className="space-y-2">
                {insights.map((insight, i) => (
                    <div
                        key={i}
                        className={`flex gap-3 items-start px-4 py-3 rounded-lg border text-sm ${COLORS[insight.type]}`}
                    >
                        <span className="mt-0.5 shrink-0">{ICONS[insight.type]}</span>
                        <span>{insight.message}</span>
                    </div>
                ))}
            </div>
        </div>
    );
}
