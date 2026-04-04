<?php

namespace App\Http\Controllers;

use App\Models\Couple;
use App\Models\OfficiantDraft;
use App\Models\User;
use App\Models\VowsDraft;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Affiche le tableau de bord admin avec les statistiques globales.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_users'    => User::count(),
                'total_couples'  => Couple::count(),
                'full_couples'   => Couple::whereNotNull('spouse_2_id')->count(),
                'vows_completed' => VowsDraft::where('status', 'completed')->count(),
                'officiants'     => OfficiantDraft::count(),
            ],
        ]);
    }

    /**
     * Affiche la liste des couples avec leurs relations chargées.
     */
    public function couples(): Response
    {
        $couples = Couple::with(['spouse1', 'spouse2', 'officiant', 'vowsDrafts', 'ceremony'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Couples', ['couples' => $couples]);
    }

    /**
     * Affiche la liste de tous les utilisateurs.
     */
    public function users(): Response
    {
        return Inertia::render('Admin/Users', [
            'users' => User::orderBy('created_at', 'desc')->get(),
        ]);
    }

    /**
     * Désactive un utilisateur en lui attribuant le rôle 'disabled'.
     * Un admin ne peut pas se désactiver lui-même.
     */
    public function disable(User $user): RedirectResponse
    {
        // Interdit de se désactiver soi-même
        abort_if(auth()->id() === $user->id, 403);

        $user->update(['role' => 'disabled']);

        return back();
    }

    /**
     * Supprime définitivement un utilisateur.
     * Un admin ne peut pas supprimer son propre compte.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Interdit de supprimer son propre compte
        abort_if(auth()->id() === $user->id, 403);

        $user->delete();

        return back();
    }
}
