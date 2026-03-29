import { useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface Props {
    draft: { id: number; generated_text: string };
}

export default function VoeuxEdit({ draft }: Props) {
    const { data, setData, put, processing } = useForm({
        generated_text: draft.generated_text ?? '',
    });

    return (
        <AuthenticatedLayout header={<h2 className="font-semibold text-xl text-gray-800">Modifier mes vœux</h2>}>
            <div className="max-w-2xl mx-auto px-4 py-8">
                <h1 className="text-3xl font-serif text-stone-800 mb-4">
                    Modifier mes vœux
                </h1>
                <p className="text-stone-500 mb-6">
                    Retouchez librement le texte. Les sauts de ligne sont préservés.
                </p>

                <textarea
                    value={data.generated_text}
                    onChange={(e) => setData('generated_text', e.target.value)}
                    rows={20}
                    className="w-full p-4 border border-stone-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:outline-none text-stone-700 leading-relaxed"
                />

                <div className="flex gap-4 mt-6">
                    <button
                        onClick={() => put(route('voeux.update'))}
                        disabled={processing}
                        className="px-6 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 disabled:opacity-50 transition-colors"
                    >
                        Enregistrer
                    </button>
                    <a
                        href={route('voeux.preview')}
                        className="px-6 py-2 border border-stone-300 text-stone-600 rounded-lg hover:bg-stone-50 transition-colors"
                    >
                        Annuler
                    </a>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
