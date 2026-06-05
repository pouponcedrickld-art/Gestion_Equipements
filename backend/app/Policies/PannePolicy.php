<?php

namespace App\Policies;

use App\Models\Panne;
use App\Models\User;

class PannePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('pannes.view_all') 
            || $user->hasPermissionTo('pannes.view_agence') 
            || $user->hasPermissionTo('pannes.view_own');
    }

    public function view(User $user, Panne $panne): bool
    {
        if ($user->hasPermissionTo('pannes.view_all')) return true;
        if ($user->hasPermissionTo('pannes.view_agence')) {
            return $user->agence_id === $panne->equipement?->agence_actuelle_id;
        }
        return $user->hasPermissionTo('pannes.view_own') && $user->id === $panne->agent?->user_id;
    }

    public function declarer(User $user): bool
    {
        return $user->hasPermissionTo('pannes.declarer');
    }

    public function recevoir(User $user, Panne $panne): bool
    {
        return $user->hasPermissionTo('pannes.recevoir') 
            && $user->agence_id === $panne->equipement?->agence_actuelle_id;
    }

    public function transmettreMaintenance(User $user, Panne $panne): bool
    {
        return $user->hasPermissionTo('pannes.transmettre_maintenance');
    }

    public function diagnostiquer(User $user, Panne $panne): bool
    {
        return $user->hasPermissionTo('pannes.diagnostiquer');
    }

    public function resoudre(User $user, Panne $panne): bool
    {
        return $user->hasPermissionTo('pannes.resoudre');
    }
}
