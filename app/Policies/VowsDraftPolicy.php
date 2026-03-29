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
}
