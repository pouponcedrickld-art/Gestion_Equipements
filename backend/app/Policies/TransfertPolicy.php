<?php

namespace App\Policies;

use App\Models\Transfert;
use App\Models\User;

class TransfertPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'gestionnaire_stock_general', 'chef_agence', 'gestionnaire_stock']);
    }

    public function view(User $user, Transfert $transfert): bool
    {
        if ($user->hasRole(['super_admin', 'gestionnaire_stock_general'])) return true;
        return $user->agence_id === $transfert->agence_source_id
            || $user->agence_id === $transfert->agence_destination_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'gestionnaire_stock_general', 'chef_agence', 'gestionnaire_stock']);
    }

    public function approuver(User $user): bool
    {
        return $user->hasRole(['super_admin', 'gestionnaire_stock_general']);
    }

    public function expedier(User $user): bool
    {
        return $user->hasRole(['super_admin', 'gestionnaire_stock_general']);
    }

    public function recevoir(User $user, Transfert $transfert): bool
    {
        return $user->hasRole(['super_admin', 'gestionnaire_stock_general', 'gestionnaire_stock'])
            && $user->agence_id === $transfert->agence_destination_id;
    }
}
