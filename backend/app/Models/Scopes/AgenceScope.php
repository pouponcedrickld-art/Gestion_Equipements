<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class AgenceScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Vérifier si l'utilisateur est Technicien ou Gestionnaire_Local
        if ($user->hasRole('Technicien') || $user->hasRole('Gestionnaire_Local')) {
            // Filtrer pour ne retourner que les données de son agence
            $builder->where('agence_actuelle_id', $user->agence_id);
        }
    }
}
