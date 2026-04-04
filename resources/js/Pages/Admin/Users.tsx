import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { router, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

interface AdminUser {
    id: number;
    name: string;
    email: string;
    role: string;
    created_at: string;
}

interface Props {
    users: AdminUser[];
}

// Badge de rôle utilisateur
function RoleBadge({ role }: { role: string }) {
    const styles: Record<string, string> = {
        admin: 'bg-purple-100 text-purple-700',
        spouse: 'bg-blue-100 text-blue-700',
        disabled: 'bg-stone-100 text-stone-500',
    };
    const labels: Record<string, string> = {
        admin: 'Admin',
        spouse: 'Époux',
        disabled: 'Désactivé',
    };

    const style = styles[role] ?? 'bg-stone-100 text-stone-600';
    const label = labels[role] ?? role;

    return (
        <span className={`inline-block text-xs px-2 py-0.5 rounded-full font-medium ${style}`}>
            {label}
        </span>
    );
}

export default function AdminUsers({ users }: Props) {
    // Récupération de l'utilisateur connecté pour masquer les actions sur soi-même
    const { auth } = usePage<PageProps>().props;
    const currentUserId = auth.user?.id;

    // Désactiver un utilisateur
    function handleDisable(userId: number) {
        router.patch(route('admin.users.disable', userId));
    }

    // Supprimer un utilisateur avec confirmation
    function handleDestroy(userId: number) {
        router.delete(route('admin.users.destroy', userId), {
            onBefore: () => confirm('Supprimer cet utilisateur ?'),
        });
    }

    return (
        <AuthenticatedLayout
            header={
                <h2 className="font-semibold text-xl text-gray-800">
                    Administration — Utilisateurs
                </h2>
            }
        >
            <div className="max-w-5xl mx-auto px-4 py-8 space-y-6">
                <h1 className="text-2xl font-serif text-stone-800">
                    Utilisateurs ({users.length})
                </h1>

                {users.length === 0 ? (
                    <div className="bg-white border border-stone-200 rounded-xl p-6 text-stone-500">
                        Aucun utilisateur enregistré.
                    </div>
                ) : (
                    <div className="bg-white border border-stone-200 rounded-xl overflow-hidden">
                        <table className="w-full text-sm">
                            <thead className="border-b border-stone-200 bg-stone-50">
                                <tr>
                                    <th className="text-left px-6 py-3 text-stone-600 font-medium">Nom</th>
                                    <th className="text-left px-6 py-3 text-stone-600 font-medium">Email</th>
                                    <th className="text-left px-6 py-3 text-stone-600 font-medium">Rôle</th>
                                    <th className="text-left px-6 py-3 text-stone-600 font-medium">Inscrit le</th>
                                    <th className="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-stone-100">
                                {users.map((user) => {
                                    const isSelf = user.id === currentUserId;

                                    return (
                                        <tr key={user.id} className="hover:bg-stone-50">
                                            <td className="px-6 py-4 text-stone-800 font-medium">
                                                {user.name}
                                                {isSelf && (
                                                    <span className="ml-2 text-xs text-stone-400">(vous)</span>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-stone-600">{user.email}</td>
                                            <td className="px-6 py-4">
                                                <RoleBadge role={user.role} />
                                            </td>
                                            <td className="px-6 py-4 text-stone-500">
                                                {new Date(user.created_at).toLocaleDateString('fr-FR')}
                                            </td>
                                            <td className="px-6 py-4">
                                                {!isSelf && (
                                                    <div className="flex items-center gap-2 justify-end">
                                                        {/* Bouton désactiver — masqué si déjà désactivé */}
                                                        {user.role !== 'disabled' && (
                                                            <button
                                                                type="button"
                                                                onClick={() => handleDisable(user.id)}
                                                                className="px-4 py-2 border border-stone-300 text-stone-600 rounded-lg hover:bg-stone-50 text-sm"
                                                            >
                                                                Désactiver
                                                            </button>
                                                        )}
                                                        {/* Bouton supprimer */}
                                                        <button
                                                            type="button"
                                                            onClick={() => handleDestroy(user.id)}
                                                            className="px-4 py-2 border border-red-200 text-red-600 rounded-lg hover:bg-red-50 text-sm"
                                                        >
                                                            Supprimer
                                                        </button>
                                                    </div>
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
