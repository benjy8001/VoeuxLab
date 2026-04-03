import { Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface Props {
    generated_text: string;
    partner_name: string;
}

export default function VoeuxPartner({ generated_text, partner_name }: Props) {
    return (
        <AuthenticatedLayout header={<h2 className="font-semibold text-xl text-gray-800">Vœux de {partner_name}</h2>}>
            <div className="max-w-2xl mx-auto px-4 py-8">
                <h1 className="text-3xl font-serif text-stone-800 mb-2">
                    Vœux de {partner_name}
                </h1>
                <p className="text-sm text-stone-400 italic mb-8">Lecture seule</p>

                <div className="bg-stone-50 border border-stone-100 rounded-xl p-6 whitespace-pre-wrap text-stone-700 leading-relaxed font-serif">
                    {generated_text || <span className="text-stone-400 italic">Aucun texte disponible.</span>}
                </div>

                <div className="mt-8">
                    <Link
                        href={route('voeux.preview')}
                        className="px-6 py-2 border border-stone-300 text-stone-600 rounded-lg hover:bg-stone-50 transition-colors"
                    >
                        ← Retour à mes vœux
                    </Link>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
