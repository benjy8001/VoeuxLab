import { router } from '@inertiajs/react';
import { useState } from 'react';

const TONES = [
    {
        key: 'balanced',
        label: 'Équilibré',
        desc: 'Un mélange naturel d\'émotion et de légèreté.',
        icon: '◐',
    },
    {
        key: 'moving',
        label: 'Émouvant',
        desc: 'Des questions profondes pour des vœux qui touchent au cœur.',
        icon: '♡',
    },
    {
        key: 'light',
        label: 'Léger & drôle',
        desc: 'Des questions qui invitent à l\'humour et à la tendresse.',
        icon: '◡',
    },
    {
        key: 'poetic',
        label: 'Poétique',
        desc: 'Des métaphores et des images pour des vœux littéraires.',
        icon: '✦',
    },
];

export default function ToneSelector() {
    const [selected, setSelected] = useState<string | null>(null);
    const [saving, setSaving] = useState(false);

    const confirm = () => {
        if (!selected) return;
        setSaving(true);
        router.post(route('voeux.tone'), { tone: selected });
    };

    return (
        <div className="max-w-2xl mx-auto px-4 py-12 text-center">
            <p className="text-amber-600 text-xs tracking-[0.3em] uppercase mb-4">Avant de commencer</p>
            <h2 className="font-['Cormorant_Garamond'] text-3xl text-stone-800 mb-3">
                Choisissez votre tonalité
            </h2>
            <p className="text-stone-500 text-sm mb-10">
                Les questions s'adapteront à l'ambiance que vous souhaitez donner à vos vœux.
            </p>

            <div className="grid grid-cols-2 gap-4 mb-8">
                {TONES.map((tone) => (
                    <button
                        key={tone.key}
                        onClick={() => setSelected(tone.key)}
                        className={`text-left p-5 rounded-xl border-2 transition-all ${
                            selected === tone.key
                                ? 'border-amber-500 bg-amber-50'
                                : 'border-stone-200 bg-white hover:border-stone-300'
                        }`}
                    >
                        <div className="text-2xl mb-2 text-amber-600">{tone.icon}</div>
                        <div className="font-['Cormorant_Garamond'] text-lg text-stone-800 mb-1">
                            {tone.label}
                        </div>
                        <div className="text-stone-500 text-xs leading-relaxed">{tone.desc}</div>
                    </button>
                ))}
            </div>

            <button
                onClick={confirm}
                disabled={!selected || saving}
                className="px-8 py-3 bg-amber-600 text-white rounded-lg hover:bg-amber-700 disabled:opacity-40 transition-colors font-['Cormorant_Garamond'] text-lg"
            >
                Commencer mes vœux →
            </button>
        </div>
    );
}
