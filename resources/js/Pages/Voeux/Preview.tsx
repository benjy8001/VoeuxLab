import { Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface Props {
    draft: { id: number; status: string; generated_text: string };
}

export default function VoeuxPreview({ draft }: Props) {
    return (
        <AuthenticatedLayout header={<h2 className="font-semibold text-xl text-gray-800">Aperçu de mes vœux</h2>}>
            <div className="max-w-2xl mx-auto px-4 py-8">
                <h1 className="text-3xl font-serif text-stone-800 mb-8">
                    Mes vœux
                </h1>

                <div className="bg-stone-50 border border-stone-200 rounded-xl p-8 whitespace-pre-wrap text-stone-700 leading-relaxed text-lg">
                    {draft.generated_text || <span className="text-stone-400 italic">Aucun texte généré.</span>}
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
