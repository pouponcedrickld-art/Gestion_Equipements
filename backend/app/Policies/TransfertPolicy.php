<?php

namespace App\Policies;

use App\Models\Transfert;
use App\Models\User;

class TransfertPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('transferts.view_all') || $user->hasPermissionTo('transferts.view_agence');
    }

    public function view(User $user, Transfert $transfert): bool
    {
        if ($user->hasPermissionTo('transferts.view_all')) return true;
        return $user->hasPermissionTo('transferts.view_agence') 
            && ($user->agence_id === $transfert->agence_source_id 
                || $user->agence_id === $transfert->agence_destination_id);
    }

    public function demander(User $user): bool
    {
        return $user->hasPermissionTo('transferts.demander');
    }

    public function approuver(User $user): bool
    {
        return $user->hasPermissionTo('transferts.approuver');
    }

    public function expedier(User $user): bool
    {
        return $user->hasPermissionTo('transferts.expedier');
    }

    public function recevoir(User $user, Transfert $transfert): bool
    {
        return $user->hasPermissionTo('transferts.recevoir') 
            && $user->agence_id === $transfert->agence_destination_id;
    }
}
