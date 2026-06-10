<?php

namespace App\Services;

use App\Models\Maintenance;
use Illuminate\Database\Eloquent\Collection;

class MaintenanceWorkflowService
{
    /**
     * Planifier une maintenance préventive
     *
     * @param array $data Données de la maintenance
     * @return Maintenance
     */
    public function planifierPreventive(array $data): Maintenance
    {
        // S'assurer que le type est préventif et le statut par défaut est "planifiee"
        $data['type_maintenance'] = $data['type_maintenance'] ?? 'preventive';
        $data['statut'] = $data['statut'] ?? 'planifiee';

        return Maintenance::create($data);
    }

    /**
     * Récupérer les maintenances par période avec filtres optionnels
     *
     * @param string $startDate Date de début (format Y-m-d ou ISO8601)
     * @param string $endDate Date de fin (format Y-m-d ou ISO8601)
     * @param array $filters Filtres optionnels (type_maintenance, statut)
     * @return Collection
     */
    public function getByPeriod(string $startDate, string $endDate, array $filters = []): Collection
    {
        $query = Maintenance::query()
            ->with(['equipement', 'panne', 'technicienUser'])
            ->whereBetween('date_prevue', [$startDate, $endDate]);

        // Appliquer les filtres optionnels
        if (isset($filters['type_maintenance']) && !empty($filters['type_maintenance'])) {
            $query->where('type_maintenance', $filters['type_maintenance']);
        }

        if (isset($filters['statut']) && !empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        return $query->orderBy('date_prevue', 'asc')->get();
    }

    /**
     * Récupérer une maintenance avec ses relations (eager loading)
     *
     * @param int $id ID de la maintenance
     * @return Maintenance
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getMaintenanceWithRelations(int $id): Maintenance
    {
        return Maintenance::with(['equipement.categorie', 'panne', 'technicienUser'])
            ->findOrFail($id);
    }
}
