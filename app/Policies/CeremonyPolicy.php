<?php

namespace App\Policies;

use App\Models\Ceremony;
use App\Models\OfficiantDraft;
use App\Models\User;

class CeremonyPolicy
{
    /**
     * Époux 1, époux 2 OU officiant assigné au couple peuvent voir la cérémonie.
     */
    public function view(User $user, Ceremony $ceremony): bool
    {
        // L'user est époux si son couple_id correspond à la cérémonie
        if ($user->couple_id === $ceremony->couple_id) {
            return true;
        }

        // L'user est officiant s'il a un OfficiantDraft pour ce couple
        return OfficiantDraft::where('user_id', $user->id)
            ->where('couple_id', $ceremony->couple_id)
            ->exists();
    }

    /**
     * Seuls les époux (membres du couple) peuvent modifier les blocs du programme.
     */
    public function updateBlocks(User $user, Ceremony $ceremony): bool
    {
        return $user->couple_id === $ceremony->couple_id;
    }

    /**
     * Seul l'officiant assigné au couple peut annoter les blocs.
     */
    public function updateOfficiantNote(User $user, Ceremony $ceremony): bool
    {
        return OfficiantDraft::where('user_id', $user->id)
            ->where('couple_id', $ceremony->couple_id)
            ->exists();
    }
}
