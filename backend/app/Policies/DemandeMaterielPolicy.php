<?php

namespace App\Policies;

use App\Models\DemandeMateriel;
use App\Models\User;

class DemandeMaterielPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('demandes.view_all') || $user->hasPermissionTo('demandes.view_agence');
    }

    public function view(User $user, DemandeMateriel $demande): bool
    {
        if ($user->hasPermissionTo('demandes.view_all')) return true;
        return $user->hasPermissionTo('demandes.view_agence') && $user->agence_id === $demande->agence_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('demandes.creer');
    }

    public function traiter(User $user): bool
    {
        return $user->hasPermissionTo('demandes.traiter');
    }

    public function annuler(User $user, DemandeMateriel $demande): bool
    {
        return $user->hasPermissionTo('demandes.annuler') && $user->id === $demande->chef_agence_id;
    }
}
