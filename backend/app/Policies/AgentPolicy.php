<?php

namespace App\Policies;

use App\Models\Agent;
use App\Models\User;

class AgentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('users.view_all') || $user->hasPermissionTo('users.view_agence') || $user->hasPermissionTo('users.view_own');
    }

    public function view(User $user, Agent $agent): bool
    {
        if ($user->hasPermissionTo('users.view_all')) return true;
        if ($user->hasPermissionTo('users.view_agence')) {
            return $user->agence_id === $agent->user?->agence_id;
        }
        return $user->hasPermissionTo('users.view_own') && $user->id === $agent->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('users.create');
    }

    public function update(User $user, Agent $agent): bool
    {
        if ($user->hasPermissionTo('users.edit')) {
            if ($user->hasRole('super_admin') || $user->hasRole('gestionnaire_stock_general')) return true;
            if ($user->hasRole('chef_agence')) return $user->agence_id === $agent->user?->agence_id;
        }
        return false;
    }

    public function delete(User $user, Agent $agent): bool
    {
        return $user->hasRole('super_admin');
    }
}
