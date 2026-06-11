<?php

namespace App\Policies;

use App\Models\DemandeMateriel;
use App\Models\User;

class DemandeMaterielPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DemandeMateriel $demande): bool
    {
        if ($user->hasRole(['super_admin', 'gestionnaire_stock_general'])) return true;
        return $user->agence_id === $demande->agence_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['chef_agence']);
    }

    public function traiter(User $user): bool
    {
        return $user->hasRole(['super_admin', 'gestionnaire_stock_general']);
    }

    public function update(User $user, DemandeMateriel $demande): bool
    {
        if ($user->hasRole(['super_admin', 'gestionnaire_stock_general'])) return true;
        return $user->id === $demande->chef_agence_id && $demande->statut === 'en attente';
    }

    public function delete(User $user, DemandeMateriel $demande): bool
    {
        if ($user->hasRole(['super_admin', 'gestionnaire_stock_general'])) return true;
        return $user->id === $demande->chef_agence_id && $demande->statut === 'en attente';
    }

    public function annuler(User $user, DemandeMateriel $demande): bool
    {
        return $user->id === $demande->chef_agence_id && $demande->statut === 'en attente';
    }
}
