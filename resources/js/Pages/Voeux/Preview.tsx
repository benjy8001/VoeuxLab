import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ReadingTimer from '@/Components/ReadingTimer';
import VowsInsights from '@/Components/VowsInsights';
import DraggableBlocks from '@/Components/DraggableBlocks';

interface Props {
    draft: { id: number; status: string; generated_text: string };
    partner_name: string | null;
    has_shared: boolean;
    partner_has_shared: boolean;
    partner_vows_readable: boolean;
    questions: { key: string; label: string; order: number }[];
    answers: Record<string, string>;
}

export default function VoeuxPreview({ draft, partner_name, has_shared, partner_has_shared, partner_vows_readable, questions, answers }: Props) {
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

                {/* Section Q&R */}
                {questions.filter(q => answers[q.key]).length > 0 && (
                    <div className="mt-10 border-t border-stone-200 pt-8">
                        <h3 className="text-lg font-serif text-stone-700 mb-6">Mes réponses</h3>
                        <div className="space-y-6">
                            {questions
                                .filter(q => answers[q.key])
                                .map(q => (
                                    <div key={q.key}>
                                        <p className="text-sm italic text-stone-400 mb-1">{q.label}</p>
                                        <p className="text-stone-700">{answers[q.key]}</p>
                                    </div>
                                ))
                            }
                        </div>
                    </div>
                )}

                {/* Bloc partage */}
                <div className="mt-8 border border-stone-200 rounded-xl p-5 bg-stone-50">
                    <h3 className="text-sm font-semibold text-stone-600 mb-3">Partage des vœux</h3>

                    {partner_vows_readable ? (
                        <div className="space-y-2">
                            <p className="text-sm text-green-700">Les vœux de votre partenaire sont disponibles.</p>
                            {partner_name && (
                                <Link
                                    href={route('voeux.partner')}
                                    className="inline-block px-4 py-2 bg-amber-600 text-white text-sm rounded-lg hover:bg-amber-700 transition-colors"
                                >
                                    Lire les vœux de {partner_name}
                                </Link>
                            )}
                        </div>
                    ) : (
                        <div className="space-y-2">
                            {!has_shared ? (
                                <button
                                    type="button"
                                    onClick={() => {
                                        if (window.confirm('Partager vos vœux permettra à votre partenaire de les lire dès qu\'il·elle aura également partagé les siens. Continuer ?')) {
                                            router.post(route('voeux.share'));
                                        }
                                    }}
                                    className="px-4 py-2 bg-amber-600 text-white text-sm rounded-lg hover:bg-amber-700 transition-colors"
                                >
                                    Partager mes vœux
                                </button>
                            ) : (
                                <p className="text-sm text-amber-700">✓ Vous avez partagé vos vœux.</p>
                            )}
                            <p className="text-xs text-stone-400">
                                {partner_has_shared
                                    ? `${partner_name ?? 'Votre partenaire'} a déjà partagé ses vœux.`
                                    : `En attente du partage de ${partner_name ?? 'votre partenaire'}.`
                                }
                            </p>
                        </div>
                    )}
                </div>

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
                        type="button"
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
