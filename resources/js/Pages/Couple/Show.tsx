import { Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

interface Props {
    couple: {
        invitation_code: string;
        ceremony_date: string | null;
        ceremony_location: string | null;
        is_full: boolean;
        spouse1_name: string;
        spouse2_name: string | null;
    };
}

export default function CoupleShow({ couple }: Props) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Notre espace couple
                </h2>
            }
        >
            <Head title="Notre espace couple" />
            <div className="max-w-lg mx-auto py-8">
                <h1 className="text-3xl font-serif text-stone-800 mb-8">
                    Notre espace couple
                </h1>

                {!couple.is_full && (
                    <div className="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                        <p className="text-stone-700 mb-2">Code d'invitation pour votre partenaire :</p>
                        <p className="text-3xl font-mono tracking-widest text-amber-700 font-bold">
                            {couple.invitation_code}
                        </p>
                    </div>
                )}

                <div className="space-y-3 text-stone-700">
                    <p>
                        <span className="text-stone-500">Partenaires :</span>{' '}
                        {couple.spouse1_name} & {couple.spouse2_name ?? '(en attente)'}
                    </p>
                    {couple.ceremony_date && (
                        <p><span className="text-stone-500">Date :</span> {couple.ceremony_date}</p>
                    )}
                    {couple.ceremony_location && (
                        <p><span className="text-stone-500">Lieu :</span> {couple.ceremony_location}</p>
                    )}
                </div>

                <div className="mt-8">
                    <Link
                        href={route('voeux.index')}
                        className="inline-block bg-amber-600 text-white px-6 py-3 rounded-lg hover:bg-amber-700"
                    >
                        Rédiger mes voeux →
                    </Link>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
