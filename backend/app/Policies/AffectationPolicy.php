<?php

namespace App\Policies;

use App\Models\Affectation;
use App\Models\User;

class AffectationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('affectations.view_all') 
            || $user->hasPermissionTo('affectations.view_agence') 
            || $user->hasPermissionTo('affectations.view_own');
    }

    public function view(User $user, Affectation $affectation): bool
    {
        if ($user->hasPermissionTo('affectations.view_all')) return true;
        if ($user->hasPermissionTo('affectations.view_agence')) {
            return $user->agence_id === $affectation->agent?->user?->agence_id;
        }
        return $user->hasPermissionTo('affectations.view_own') && $user->id === $affectation->agent?->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('affectations.creer');
    }

    public function retourner(User $user, Affectation $affectation): bool
    {
        return $user->hasPermissionTo('affectations.retourner');
    }

    public function annuler(User $user, Affectation $affectation): bool
    {
        return $user->hasPermissionTo('affectations.annuler');
    }
}
