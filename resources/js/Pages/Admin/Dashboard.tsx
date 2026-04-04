import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface Stats {
    total_users: number;
    total_couples: number;
    full_couples: number;
    vows_completed: number;
    officiants: number;
}

interface Props {
    stats: Stats;
}

// Carte de statistique individuelle
function StatCard({ label, value }: { label: string; value: number }) {
    return (
        <div className="bg-white border border-stone-200 rounded-xl p-6">
            <p className="text-sm text-stone-500 mb-1">{label}</p>
            <p className="text-3xl font-semibold text-stone-800">{value}</p>
        </div>
    );
}

export default function AdminDashboard({ stats }: Props) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="font-semibold text-xl text-gray-800">
                    Administration — Tableau de bord
                </h2>
            }
        >
            <div className="max-w-4xl mx-auto px-4 py-8 space-y-6">
                <h1 className="text-2xl font-serif text-stone-800">Statistiques</h1>

                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <StatCard label="Utilisateurs" value={stats.total_users} />
                    <StatCard label="Couples" value={stats.total_couples} />
                    <StatCard label="Couples complets" value={stats.full_couples} />
                    <StatCard label="Vœux finalisés" value={stats.vows_completed} />
                    <StatCard label="Officiants" value={stats.officiants} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
