<?php

namespace App\Services;

use App\Models\Maintenance;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MaintenanceWorkflowService
{
    /**
     * Planifier une maintenance préventive
     *
     * @param array $data Données de la maintenance
     * @return Maintenance
     * @throws \Exception Si une maintenance existe déjà pour le même équipement à la même date
     */
    public function planifierPreventive(array $data): Maintenance
    {
        return DB::transaction(function () use ($data) {
            // Vérifier les doublons: même équipement et même date prévue
            $this->checkForDuplicate($data['equipement_id'], $data['date_prevue']);

            // S'assurer que le type est préventif et le statut par défaut est "planifiee"
            $data['type_maintenance'] = $data['type_maintenance'] ?? 'preventive';
            $data['statut'] = $data['statut'] ?? 'planifiee';

            return Maintenance::create($data);
        });
    }

    /**
     * Vérifie s'il existe déjà une maintenance pour le même équipement à la même date
     *
     * @param int $equipementId
     * @param string $datePrevue
     * @return void
     * @throws \Exception
     */
    private function checkForDuplicate(int $equipementId, string $datePrevue): void
    {
        $dateOnly = substr($datePrevue, 0, 10);

        $exists = Maintenance::where('equipement_id', $equipementId)
            ->whereDate('date_prevue', $dateOnly)
            ->where('statut', '!=', 'terminee')
            ->exists();

        if ($exists) {
            throw new \Exception('Une maintenance est déjà planifiée pour cet équipement à cette date.');
        }
    }

    /**
     * Récupérer les maintenances par période avec filtres optionnels et scope agence
     *
     * @param string $startDate Date de début (format Y-m-d ou ISO8601)
     * @param string $endDate Date de fin (format Y-m-d ou ISO8601)
     * @param array $filters Filtres optionnels (type_maintenance, statut)
     * @param int|null $agenceId ID de l'agence pour filtrer
     * @return Collection
     */
    public function getByPeriod(string $startDate, string $endDate, array $filters = [], ?int $agenceId = null): Collection
    {
        $query = Maintenance::query()
            ->with(['equipement', 'panne', 'technicienUser'])
            ->whereBetween('date_prevue', [$startDate, $endDate]);

        // Appliquer le scope agence
        if ($agenceId) {
            $query->forAgence($agenceId);
        }

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

    /**
     * Démarrer une maintenance
     *
     * @param int $maintenanceId
     * @param array $data
     * @return Maintenance
     */
    public function start(int $maintenanceId, array $data = []): Maintenance
    {
        return DB::transaction(function () use ($maintenanceId, $data) {
            $maintenance = Maintenance::findOrFail($maintenanceId);
            $maintenance->update([
                'statut' => 'en_cours',
                'date_debut' => now(),
                ...$data,
            ]);

            return $maintenance;
        });
    }

    /**
     * Terminer une maintenance
     *
     * @param int $maintenanceId
     * @param array $data
     * @return Maintenance
     */
    public function complete(int $maintenanceId, array $data = []): Maintenance
    {
        return DB::transaction(function () use ($maintenanceId, $data) {
            $maintenance = Maintenance::findOrFail($maintenanceId);
            $maintenance->update([
                'statut' => 'terminee',
                'date_fin' => now(),
                ...$data,
            ]);

            return $maintenance;
        });
    }

    /**
     * Mettre à jour une maintenance
     *
     * @param int $maintenanceId
     * @param array $data
     * @return Maintenance
     */
    public function update(int $maintenanceId, array $data): Maintenance
    {
        return DB::transaction(function () use ($maintenanceId, $data) {
            $maintenance = Maintenance::findOrFail($maintenanceId);

            // Vérifier les doublons si on change la date ou l'équipement
            if (isset($data['equipement_id']) || isset($data['date_prevue'])) {
                $equipId = $data['equipement_id'] ?? $maintenance->equipement_id;
                $datePrev = $data['date_prevue'] ?? $maintenance->date_prevue->toIso8601String();
                $this->checkForDuplicate($equipId, $datePrev);
            }

            $maintenance->update($data);

            return $maintenance;
        });
    }

    /**
     * Supprimer une maintenance
     *
     * @param int $maintenanceId
     * @return void
     */
    public function delete(int $maintenanceId): void
    {
        $maintenance = Maintenance::findOrFail($maintenanceId);
        $maintenance->delete();
    }
}
