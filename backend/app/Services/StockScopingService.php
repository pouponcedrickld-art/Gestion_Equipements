<?php
namespace App\Services;
use Illuminate\Database\Eloquent\Builder;

class StockScopingService
{
    public static function scopeEquipements(Builder $query, $user): Builder
    {
        if ($user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
            return $query;
        }
        if ($user->hasRole(['chef_agence', 'gestionnaire_stock', 'technicien_maintenance'])) {
            return $query->where('agence_actuelle_id', $user->agence_id);
        }
        return $query->whereHas('affectations', fn($q) => $q->where('agent_id', $user->agent?->id)->where('statut', 'active'));
    }

    public static function scopeUsers(Builder $query, $user): Builder
    {
        if ($user->hasRole('super_admin')) return $query;
        return $query->where('agence_id', $user->agence_id);
    }

    public static function scopeAgences(Builder $query, $user): Builder
    {
        if ($user->hasRole('super_admin')) return $query;
        return $query->where('id', $user->agence_id)->orWhere('parent_id', $user->agence_id);
    }
}
