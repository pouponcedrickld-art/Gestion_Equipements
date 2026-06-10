<?php

namespace App\Policies;

use App\Models\Equipement;
use App\Models\User;

class EquipementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('equipements.view_global') 
            || $user->hasPermissionTo('equipements.view_agence') 
            || $user->hasPermissionTo('equipements.view_own');
    }

    public function view(User $user, Equipement $equipement): bool
    {
        if ($user->hasPermissionTo('equipements.view_global')) return true;
        if ($user->hasPermissionTo('equipements.view_agence')) {
            return $user->agence_id === $equipement->agence_actuelle_id 
                || $user->agence_id === $equipement->agence_proprietaire_id;
        }
        return $user->hasPermissionTo('equipements.view_own') && $user->id === $equipement->agent_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('equipements.create');
    }

    public function update(User $user, Equipement $equipement): bool
    {
        if ($user->hasPermissionTo('equipements.edit')) {
            if ($user->hasRole('super_admin') || $user->hasRole('gestionnaire_stock_general')) return true;
            if ($user->hasRole('gestionnaire_stock')) {
                return $user->agence_id === $equipement->agence_actuelle_id;
            }
        }
        return false;
    }

    public function delete(User $user, Equipement $equipement): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('gestionnaire_stock_general');
    }

    public function import(User $user): bool
    {
        return $user->hasPermissionTo('equipements.import');
    }

    public function genererQr(User $user): bool
    {
        return $user->hasPermissionTo('equipements.generer_qr');
    }
}
