import { Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ProgressBar from '@/Components/ProgressBar';

interface CoupleData {
    invitation_code: string;
    is_full: boolean;
    spouse1_name: string;
    spouse2_name: string | null;
    ceremony_date: string | null;
    ceremony_location: string | null;
}

interface VowsProgressData {
    current_step: number;
    total_steps: number;
    status: string;
}

interface Props {
    couple: CoupleData | null;
    vows_progress: VowsProgressData | null;
}

export default function Dashboard({ couple, vows_progress }: Props) {
    return (
        <AuthenticatedLayout header={<h2 className="font-semibold text-xl text-gray-800">Tableau de bord</h2>}>
            <div className="max-w-2xl mx-auto px-4 py-8 space-y-6">
                <h1 className="text-3xl font-serif text-stone-800">
                    Bienvenue 👋
                </h1>

                {/* Couple section */}
                {!couple ? (
                    <div className="bg-amber-50 border border-amber-200 rounded-xl p-6">
                        <p className="text-stone-700 mb-4">
                            Commencez par créer votre espace couple pour rédiger vos vœux.
                        </p>
                        <div className="flex gap-3 flex-wrap">
                            <Link
                                href={route('couple.create')}
                                className="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 text-sm"
                            >
                                Créer un espace couple
                            </Link>
                            <Link
                                href={route('couple.join')}
                                className="px-4 py-2 border border-stone-300 text-stone-600 rounded-lg hover:bg-stone-50 text-sm"
                            >
                                Rejoindre avec un code
                            </Link>
                        </div>
                    </div>
                ) : (
                    <div className="bg-white border border-stone-200 rounded-xl p-6">
                        <h2 className="text-lg font-semibold text-stone-700 mb-3">Notre couple</h2>
                        <p className="text-stone-600">
                            {couple.spouse1_name} &amp; {couple.spouse2_name ?? (
                                <span className="text-stone-400 italic">en attente…</span>
                            )}
                        </p>
                        {couple.ceremony_date && (
                            <p className="text-stone-500 text-sm mt-1">📅 {couple.ceremony_date}</p>
                        )}
                        {couple.ceremony_location && (
                            <p className="text-stone-500 text-sm mt-0.5">📍 {couple.ceremony_location}</p>
                        )}
                        {!couple.is_full && (
                            <div className="mt-4 bg-stone-50 rounded-lg p-3">
                                <p className="text-xs text-stone-500 mb-1">Code d'invitation à partager</p>
                                <p className="font-mono text-xl tracking-widest text-amber-700 font-bold">
                                    {couple.invitation_code}
                                </p>
                            </div>
                        )}
                    </div>
                )}

                {/* Vows progress section */}
                {vows_progress && (
                    <div className="bg-white border border-stone-200 rounded-xl p-6">
                        <h2 className="text-lg font-semibold text-stone-700 mb-4">Mes vœux</h2>
                        {vows_progress.status === 'completed' ? (
                            <div>
                                <p className="text-green-700 text-sm mb-4">✓ Vœux rédigés</p>
                                <div className="flex gap-3 flex-wrap">
                                    <Link
                                        href={route('voeux.preview')}
                                        className="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 text-sm"
                                    >
                                        Voir mes vœux
                                    </Link>
                                    <a
                                        href={route('voeux.export')}
                                        className="px-4 py-2 border border-stone-300 text-stone-600 rounded-lg hover:bg-stone-50 text-sm"
                                    >
                                        Télécharger PDF
                                    </a>
                                </div>
                            </div>
                        ) : (
                            <div>
                                <ProgressBar
                                    current={vows_progress.current_step}
                                    total={vows_progress.total_steps}
                                />
                                <Link
                                    href={route('voeux.index')}
                                    className="mt-4 inline-block px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 text-sm"
                                >
                                    Continuer mes vœux
                                </Link>
                            </div>
                        )}
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
