<?php

namespace App\Policies;

use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MaintenancePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance', 'gestionnaire_stock']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Maintenance $maintenance): bool
    {
        // If user is super admin or gestionnaire_stock_general, they can view all
        if ($user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
            return true;
        }

        // Otherwise, check if the maintenance's equipement is in the user's agence
        return $maintenance->equipement && $maintenance->equipement->agence_actuelle_id === $user->agence_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function planifier(User $user): bool
    {
        return $user->hasRole(['super_admin', 'gestionnaire_stock_general', 'gestionnaire_stock']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Maintenance $maintenance): bool
    {
        return $this->planifier($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Maintenance $maintenance): bool
    {
        return $this->planifier($user);
    }

    /**
     * Determine whether the user can start the maintenance.
     */
    public function start(User $user, Maintenance $maintenance): bool
    {
        return $user->hasRole(['super_admin', 'technicien_maintenance']);
    }

    /**
     * Determine whether the user can complete the maintenance.
     */
    public function complete(User $user, Maintenance $maintenance): bool
    {
        return $user->hasRole(['super_admin', 'technicien_maintenance']);
    }
}
