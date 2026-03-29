import { Link, Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface Props {
    draft: { id: number; status: string; generated_text: string };
}

export default function OfficiantPreview({ draft }: Props) {
    return (
        <AuthenticatedLayout header={<h2 className="font-semibold text-xl text-gray-800">Mon discours</h2>}>
            <Head title="Mon discours de cérémonie" />
            <div className="max-w-2xl mx-auto px-4 py-8">
                <h1 className="font-['Cormorant_Garamond'] text-3xl text-stone-800 mb-8">
                    Mon discours
                </h1>
                <div className="bg-stone-50 border border-stone-200 rounded-xl p-8 whitespace-pre-wrap text-stone-700 leading-relaxed">
                    {draft.generated_text || <span className="italic text-stone-400">Aucun texte généré.</span>}
                </div>
                <div className="flex gap-4 mt-8">
                    <Link
                        href={route('officiant.index')}
                        className="px-6 py-2 border border-stone-300 text-stone-600 rounded-lg hover:bg-stone-50 transition-colors"
                    >
                        Revoir mes réponses
                    </Link>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
