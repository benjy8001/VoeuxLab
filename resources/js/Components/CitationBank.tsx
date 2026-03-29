import { useState } from 'react';
import { CITATION_CATEGORIES, type Citation } from '@/data/citations';

interface Props {
    onInsert: (text: string) => void;
}

export default function CitationBank({ onInsert }: Props) {
    const [open, setOpen] = useState(false);
    const [activeCategory, setActiveCategory] = useState(CITATION_CATEGORIES[0].key);

    const category = CITATION_CATEGORIES.find((c) => c.key === activeCategory)!;

    const handleInsert = (citation: Citation) => {
        onInsert(`« ${citation.text} » — ${citation.author}`);
        setOpen(false);
    };

    return (
        <>
            {/* Bouton flottant */}
            <button
                onClick={() => setOpen(true)}
                title="Banque de citations"
                className="fixed bottom-6 right-6 z-40 w-12 h-12 bg-amber-600 text-white rounded-full shadow-lg hover:bg-amber-700 transition-colors flex items-center justify-center text-xl"
                aria-label="Ouvrir la banque de citations"
            >
                ✦
            </button>

            {/* Modal */}
            {open && (
                <div className="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
                    {/* Overlay */}
                    <div
                        className="absolute inset-0 bg-black/30 backdrop-blur-sm"
                        onClick={() => setOpen(false)}
                    />

                    {/* Panel */}
                    <div className="relative bg-white rounded-t-2xl sm:rounded-2xl w-full max-w-lg max-h-[80vh] flex flex-col shadow-2xl">
                        {/* Header */}
                        <div className="flex items-center justify-between px-6 py-4 border-b border-stone-200">
                            <h3 className="font-['Cormorant_Garamond'] text-xl text-stone-800">
                                Banque de citations
                            </h3>
                            <button
                                onClick={() => setOpen(false)}
                                className="text-stone-400 hover:text-stone-600 text-2xl leading-none"
                                aria-label="Fermer"
                            >
                                ×
                            </button>
                        </div>

                        {/* Catégories */}
                        <div className="flex gap-1 px-4 pt-3 pb-2 border-b border-stone-100 overflow-x-auto">
                            {CITATION_CATEGORIES.map((cat) => (
                                <button
                                    key={cat.key}
                                    onClick={() => setActiveCategory(cat.key)}
                                    className={`flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm whitespace-nowrap transition-colors ${
                                        activeCategory === cat.key
                                            ? 'bg-amber-100 text-amber-700 font-medium'
                                            : 'text-stone-500 hover:bg-stone-100'
                                    }`}
                                >
                                    <span>{cat.icon}</span>
                                    {cat.label}
                                </button>
                            ))}
                        </div>

                        {/* Liste des citations */}
                        <div className="overflow-y-auto flex-1 p-4 space-y-3">
                            {category.citations.map((citation) => (
                                <button
                                    key={citation.id}
                                    onClick={() => handleInsert(citation)}
                                    className="w-full text-left p-4 rounded-xl border border-stone-200 hover:border-amber-300 hover:bg-amber-50 transition-all group"
                                >
                                    <p className="text-stone-700 italic text-sm leading-relaxed mb-1">
                                        « {citation.text} »
                                    </p>
                                    <p className="text-stone-400 text-xs">— {citation.author}</p>
                                    <p className="text-amber-600 text-xs mt-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        Cliquer pour insérer →
                                    </p>
                                </button>
                            ))}
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
