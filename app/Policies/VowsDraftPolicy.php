<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VowsDraft;

class VowsDraftPolicy
{
    public function view(User $user, VowsDraft $draft): bool
    {
        return $user->id === $draft->user_id;
    }

    public function update(User $user, VowsDraft $draft): bool
    {
        return $user->id === $draft->user_id;
    }

    /**
     * Un époux peut lire les vœux du partenaire si les deux ont partagé
     * ou si la date de cérémonie est passée.
     */
    public function viewPartner(User $user, VowsDraft $partnerDraft): bool
    {
        $couple = $user->couple;
        if (!$couple) {
            return false;
        }

        // Le draft cible doit appartenir au partenaire du même couple
        if ($partnerDraft->user_id === $user->id) {
            return false;
        }
        if ($partnerDraft->couple_id !== $couple->id) {
            return false;
        }

        $myDraft = VowsDraft::where('user_id', $user->id)
            ->where('couple_id', $couple->id)
            ->first();

        return $myDraft?->isReadableByPartner($couple) ?? false;
    }
}
