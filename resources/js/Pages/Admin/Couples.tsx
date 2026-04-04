import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface Spouse {
    id: number;
    name: string;
}

interface Officiant {
    id: number;
    name: string;
}

interface VowsDraft {
    id: number;
    user_id: number;
    status: string;
    current_step: number;
}

interface Couple {
    id: number;
    spouse1?: Spouse;
    spouse2?: Spouse | null;
    officiant?: Officiant | null;
    ceremony_date?: string | null;
    ceremony_location?: string | null;
    vows_drafts?: VowsDraft[];
}

interface Props {
    couples: Couple[];
}

// Badge de statut des vœux
function VowsBadge({ status }: { status: string }) {
    const styles: Record<string, string> = {
        completed: 'bg-green-100 text-green-700',
        draft: 'bg-amber-100 text-amber-700',
    };
    const labels: Record<string, string> = {
        completed: 'Finalisés',
        draft: 'En cours',
    };

    const style = styles[status] ?? 'bg-stone-100 text-stone-600';
    const label = labels[status] ?? status;

    return (
        <span className={`inline-block text-xs px-2 py-0.5 rounded-full font-medium ${style}`}>
            {label}
        </span>
    );
}

export default function AdminCouples({ couples }: Props) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="font-semibold text-xl text-gray-800">
                    Administration — Couples
                </h2>
            }
        >
            <div className="max-w-5xl mx-auto px-4 py-8 space-y-6">
                <h1 className="text-2xl font-serif text-stone-800">
                    Couples ({couples.length})
                </h1>

                {couples.length === 0 ? (
                    <div className="bg-white border border-stone-200 rounded-xl p-6 text-stone-500">
                        Aucun couple enregistré.
                    </div>
                ) : (
                    <div className="space-y-4">
                        {couples.map((couple) => (
                            <div
                                key={couple.id}
                                className="bg-white border border-stone-200 rounded-xl p-6"
                            >
                                {/* Époux */}
                                <div className="flex flex-wrap items-center gap-2 mb-3">
                                    <span className="font-semibold text-stone-800">
                                        {couple.spouse1?.name ?? (
                                            <span className="italic text-stone-400">Époux 1 manquant</span>
                                        )}
                                    </span>
                                    <span className="text-stone-400">&amp;</span>
                                    <span className="font-semibold text-stone-800">
                                        {couple.spouse2?.name ?? (
                                            <span className="italic text-stone-400">En attente…</span>
                                        )}
                                    </span>
                                </div>

                                {/* Infos cérémonie */}
                                <div className="text-sm text-stone-500 space-y-0.5 mb-3">
                                    {couple.ceremony_date && (
                                        <p>Date : {couple.ceremony_date}</p>
                                    )}
                                    {couple.ceremony_location && (
                                        <p>Lieu : {couple.ceremony_location}</p>
                                    )}
                                    {couple.officiant && (
                                        <p>Officiant : {couple.officiant.name}</p>
                                    )}
                                </div>

                                {/* Vœux */}
                                {couple.vows_drafts && couple.vows_drafts.length > 0 && (
                                    <div className="flex flex-wrap gap-2">
                                        {couple.vows_drafts.map((draft) => {
                                            const spouseName =
                                                draft.user_id === couple.spouse1?.id
                                                    ? couple.spouse1?.name
                                                    : couple.spouse2?.name;
                                            return (
                                                <div
                                                    key={draft.id}
                                                    className="flex items-center gap-1.5 text-xs text-stone-600"
                                                >
                                                    <span>{spouseName ?? `Époux #${draft.user_id}`}</span>
                                                    <VowsBadge status={draft.status} />
                                                    {draft.status !== 'completed' && (
                                                        <span className="text-stone-400">
                                                            (étape {draft.current_step})
                                                        </span>
                                                    )}
                                                </div>
                                            );
                                        })}
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
