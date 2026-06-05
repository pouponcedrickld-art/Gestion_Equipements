<?php

namespace App\Policies;

use App\Models\Perte;
use App\Models\User;

class PertePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('pertes.view_all') || $user->hasPermissionTo('pertes.view_agence');
    }

    public function view(User $user, Perte $perte): bool
    {
        if ($user->hasPermissionTo('pertes.view_all')) return true;
        if ($user->hasPermissionTo('pertes.view_agence')) {
            return $user->agence_id === $perte->agent?->user?->agence_id;
        }
        return $user->hasPermissionTo('pertes.view_own') && $user->id === $perte->agent?->user_id;
    }

    public function declarer(User $user): bool
    {
        return $user->hasPermissionTo('pertes.declarer');
    }

    public function valider(User $user, Perte $perte): bool
    {
        return $user->hasPermissionTo('pertes.valider');
    }

    public function cloturer(User $user, Perte $perte): bool
    {
        return $user->hasPermissionTo('pertes.cloturer');
    }
}
