import { useState, useEffect } from 'react';

interface Props {
    questionKey: string;
    tone: string;
    onInsert: (text: string) => void;
}

type Status = 'idle' | 'loading' | 'loaded' | 'error';

export default function SuggestionsPanel({ questionKey, tone, onInsert }: Props) {
    const [status, setStatus]           = useState<Status>('idle');
    const [suggestions, setSuggestions] = useState<string[]>([]);

    // Reset vers idle quand la question change
    useEffect(() => {
        setStatus('idle');
        setSuggestions([]);
    }, [questionKey]);

    const load = async () => {
        setStatus('loading');
        try {
            const url = new URL(route('voeux.suggestions'), window.location.origin);
            url.searchParams.set('question_key', questionKey);
            url.searchParams.set('tone', tone);

            const res = await fetch(url.toString(), { credentials: 'include' });
            if (!res.ok) throw new Error('Erreur réseau');

            const data = await res.json();
            setSuggestions(data.suggestions ?? []);
            setStatus('loaded');
        } catch {
            setStatus('error');
        }
    };

    if (status === 'idle') {
        return (
            <button
                type="button"
                onClick={load}
                className="text-sm text-stone-400 hover:text-amber-600 underline underline-offset-2 transition-colors"
            >
                Voir des suggestions
            </button>
        );
    }

    if (status === 'loading') {
        return (
            <button type="button" disabled className="text-sm text-stone-300 cursor-not-allowed">
                Chargement…
            </button>
        );
    }

    if (status === 'error') {
        return (
            <span className="text-sm text-stone-400 italic">Suggestions indisponibles</span>
        );
    }

    // status === 'loaded'
    if (suggestions.length === 0) {
        return (
            <span className="text-sm text-stone-400 italic">Aucune suggestion pour cette question.</span>
        );
    }

    return (
        <div className="space-y-2">
            {suggestions.map((text, i) => (
                <button
                    key={i}
                    type="button"
                    onClick={() => onInsert(text)}
                    className="block w-full text-left px-3 py-2 text-sm text-stone-600 italic border border-stone-200 rounded-lg hover:border-amber-300 hover:bg-amber-50 hover:text-stone-800 transition-all"
                >
                    {text}
                </button>
            ))}
        </div>
    );
}
