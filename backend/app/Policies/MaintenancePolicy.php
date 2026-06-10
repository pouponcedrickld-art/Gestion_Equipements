<?php

namespace App\Policies;

use App\Models\Maintenance;
use App\Models\User;

class MaintenancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('maintenances.view_all') || $user->hasPermissionTo('maintenances.view_agence');
    }

    public function view(User $user, Maintenance $maintenance): bool
    {
        if ($user->hasPermissionTo('maintenances.view_all')) return true;
        return $user->hasPermissionTo('maintenances.view_agence') 
            && $user->agence_id === $maintenance->equipement?->agence_actuelle_id;
    }

    public function planifier(User $user): bool
    {
        return $user->hasPermissionTo('maintenances.planifier');
    }

    public function realiser(User $user, Maintenance $maintenance): bool
    {
        return $user->hasPermissionTo('maintenances.realiser') && $user->id === $maintenance->technicien_id;
    }

    public function cloturer(User $user, Maintenance $maintenance): bool
    {
        return $user->hasPermissionTo('maintenances.cloturer');
    }
}
