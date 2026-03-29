import { useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function CoupleCreate() {
    const { data, setData, post, processing, errors } = useForm({
        ceremony_date: '',
        ceremony_location: '',
    });

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Créer votre espace couple
                </h2>
            }
        >
            <Head title="Créer votre espace couple" />
            <div className="max-w-lg mx-auto py-8">
                <h1 className="text-3xl font-serif text-stone-800 mb-8">
                    Créez votre espace couple
                </h1>
                <form onSubmit={(e) => { e.preventDefault(); post(route('couple.store')); }}>
                    <div className="mb-4">
                        <label className="block text-sm text-stone-600 mb-1">
                            Date de la cérémonie
                        </label>
                        <input
                            type="date"
                            value={data.ceremony_date}
                            onChange={(e) => setData('ceremony_date', e.target.value)}
                            className="w-full border border-stone-300 rounded-lg p-3"
                        />
                        {errors.ceremony_date && <p className="text-red-500 text-sm mt-1">{errors.ceremony_date}</p>}
                    </div>
                    <div className="mb-6">
                        <label className="block text-sm text-stone-600 mb-1">
                            Lieu de la cérémonie
                        </label>
                        <input
                            type="text"
                            value={data.ceremony_location}
                            onChange={(e) => setData('ceremony_location', e.target.value)}
                            placeholder="Ex: Château de Versailles"
                            className="w-full border border-stone-300 rounded-lg p-3"
                        />
                    </div>
                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full bg-amber-600 text-white py-3 rounded-lg hover:bg-amber-700 disabled:opacity-50"
                    >
                        Créer notre espace
                    </button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
