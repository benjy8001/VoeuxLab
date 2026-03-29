<?php

namespace App\Policies;

use App\Models\{OfficiantDraft, User};

class OfficiantDraftPolicy
{
    public function view(User $user, OfficiantDraft $draft): bool
    {
        return $user->id === $draft->user_id;
    }

    public function update(User $user, OfficiantDraft $draft): bool
    {
        return $user->id === $draft->user_id;
    }
}
