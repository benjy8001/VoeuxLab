import { useForm, Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function OfficiantJoin() {
    const { data, setData, post, processing, errors } = useForm({ invitation_code: '' });

    return (
        <AuthenticatedLayout header={<h2 className="font-semibold text-xl text-gray-800">Mode officiant·e</h2>}>
            <Head title="Mode officiant·e" />
            <div className="max-w-md mx-auto px-4 py-12 text-center">
                <p className="text-amber-600 text-xs tracking-[0.3em] uppercase mb-4">Officiant·e</p>
                <h1 className="font-['Cormorant_Garamond'] text-3xl text-stone-800 mb-3">
                    Préparer le discours
                </h1>
                <p className="text-stone-500 text-sm mb-8">
                    Entrez le code d'invitation du couple pour accéder à votre parcours guidé.
                </p>
                <form onSubmit={(e) => { e.preventDefault(); post(route('officiant.store')); }}>
                    <input
                        type="text"
                        value={data.invitation_code}
                        onChange={(e) => setData('invitation_code', e.target.value.toUpperCase())}
                        placeholder="CODE"
                        maxLength={8}
                        className="w-full text-center text-2xl tracking-widest border border-stone-300 rounded-lg p-4 mb-4 uppercase font-mono"
                    />
                    {errors.invitation_code && <p className="text-red-500 text-sm mb-4">{errors.invitation_code}</p>}
                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full bg-amber-600 text-white py-3 rounded-lg hover:bg-amber-700 disabled:opacity-50 transition-colors"
                    >
                        Commencer
                    </button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
