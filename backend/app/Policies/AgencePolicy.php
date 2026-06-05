<?php

namespace App\Policies;

use App\Models\Agence;
use App\Models\User;

class AgencePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('agences.view_all') || $user->hasPermissionTo('agences.view_own');
    }

    public function view(User $user, Agence $agence): bool
    {
        if ($user->hasPermissionTo('agences.view_all')) return true;
        return $user->hasPermissionTo('agences.view_own') && $user->agence_id === $agence->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('agences.create');
    }

    public function update(User $user, Agence $agence): bool
    {
        if ($user->hasPermissionTo('agences.edit')) {
            if ($user->hasRole('super_admin')) return true;
            return $user->agence_id === $agence->id;
        }
        return false;
    }

    public function delete(User $user, Agence $agence): bool
    {
        return $user->hasPermissionTo('agences.delete') && $user->hasRole('super_admin');
    }
}
