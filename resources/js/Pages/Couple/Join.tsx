import { useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

interface Props {
    initial_code?: string | null;
}

export default function CoupleJoin({ initial_code }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        invitation_code: initial_code ?? '',
    });

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Rejoindre un couple
                </h2>
            }
        >
            <Head title="Rejoindre un couple" />
            <div className="max-w-md mx-auto py-8 text-center">
                <h1 className="text-3xl font-serif text-stone-800 mb-4">
                    Rejoindre votre partenaire
                </h1>
                <p className="text-stone-500 mb-8">
                    Entrez le code d'invitation partagé par votre partenaire.
                </p>
                <form onSubmit={(e) => { e.preventDefault(); post(route('couple.attach')); }}>
                    <input
                        type="text"
                        value={data.invitation_code}
                        onChange={(e) => setData('invitation_code', e.target.value.toUpperCase())}
                        placeholder="XXXXXXXX"
                        maxLength={8}
                        className="w-full text-center text-2xl tracking-widest border border-stone-300 rounded-lg p-4 mb-4 uppercase"
                    />
                    {errors.invitation_code && (
                        <p className="text-red-500 text-sm mb-4">{errors.invitation_code}</p>
                    )}
                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full bg-amber-600 text-white py-3 rounded-lg hover:bg-amber-700 disabled:opacity-50"
                    >
                        Rejoindre
                    </button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
