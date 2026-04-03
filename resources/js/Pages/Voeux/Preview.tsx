import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ReadingTimer from '@/Components/ReadingTimer';
import VowsInsights from '@/Components/VowsInsights';
import DraggableBlocks from '@/Components/DraggableBlocks';

interface Props {
    draft: { id: number; status: string; generated_text: string };
    partner_name: string | null;
}

export default function VoeuxPreview({ draft, partner_name }: Props) {
    const [text, setText] = useState(draft.generated_text ?? '');

    const handleReorder = (newText: string) => {
        setText(newText);
        router.put(route('voeux.update'), { generated_text: newText }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout header={<h2 className="font-semibold text-xl text-gray-800">Aperçu de mes vœux</h2>}>
            <div className="max-w-2xl mx-auto px-4 py-8">
                <h1 className="text-3xl font-serif text-stone-800 mb-8">
                    Mes vœux
                </h1>

                {text ? (
                    <>
                        <DraggableBlocks initialText={text} onChange={handleReorder} />
                        <p className="text-xs text-stone-400 mt-3 text-center">
                            Glissez-déposez les blocs pour réordonner — fonctionne aussi au toucher
                        </p>
                    </>
                ) : (
                    <div className="bg-stone-50 border border-stone-200 rounded-xl p-8 text-stone-400 italic">
                        Aucun texte généré.
                    </div>
                )}

                <ReadingTimer text={text} />
                <VowsInsights text={text} partnerName={partner_name} />

                <div className="flex flex-wrap gap-4 mt-8">
                    <Link
                        href={route('voeux.edit')}
                        className="px-6 py-2 border border-stone-300 text-stone-600 rounded-lg hover:bg-stone-50 transition-colors"
                    >
                        Modifier le texte
                    </Link>
                    <Link
                        href={route('voeux.index')}
                        className="px-6 py-2 border border-stone-300 text-stone-600 rounded-lg hover:bg-stone-50 transition-colors"
                    >
                        Revoir mes réponses
                    </Link>
                    <button
                        onClick={() => {
                            if (window.confirm('Cette action remplacera le texte actuel par une nouvelle génération depuis vos réponses. Continuer ?')) {
                                router.post(route('voeux.regenerate'));
                            }
                        }}
                        className="px-6 py-2 border border-stone-300 text-stone-600 rounded-lg hover:bg-stone-50 transition-colors"
                    >
                        Régénérer depuis mes réponses
                    </button>
                    <a
                        href={route('voeux.export')}
                        className="px-6 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors"
                    >
                        Télécharger PDF
                    </a>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
