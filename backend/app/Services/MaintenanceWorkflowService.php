<?php

namespace App\Services;

use App\Models\Equipement;
use App\Models\Maintenance;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
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

            $maintenance = Maintenance::create($data);

            // Send notification
            $this->sendMaintenanceCreatedNotification($maintenance);

            return $maintenance;
        });
    }

    /**
     * Planifier une maintenance corrective (liée à une panne)
     *
     * @param array $data Données de la maintenance
     * @return Maintenance
     * @throws \Exception
     */
    public function planifierCorrective(array $data): Maintenance
    {
        return DB::transaction(function () use ($data) {
            // Vérifier les doublons: même équipement et même date prévue
            $this->checkForDuplicate($data['equipement_id'], $data['date_prevue']);

            // S'assurer que le type est correctif et le statut par défaut est "planifiee"
            $data['type_maintenance'] = 'corrective';
            $data['statut'] = $data['statut'] ?? 'planifiee';

            $maintenance = Maintenance::create($data);

            // Si une panne est liée, la passer en en_maintenance
            if (isset($data['panne_id'])) {
                $panne = \App\Models\Panne::findOrFail($data['panne_id']);
                $panne->update(['statut' => 'en_maintenance']);
            }

            // Send notification
            $this->sendMaintenanceCreatedNotification($maintenance);

            return $maintenance;
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

            // Mettre à jour le statut de l'équipement
            $equipement = Equipement::findOrFail($maintenance->equipement_id);
            $currentUser = Auth::user();
            $equipement->changerStatut('en_maintenance', $currentUser->id, "Démarrage de maintenance #{$maintenance->id}");

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

            // Mettre à jour le statut de l'équipement
            $equipement = Equipement::findOrFail($maintenance->equipement_id);
            $currentUser = Auth::user();
            $equipement->changerStatut('en_service', $currentUser->id, "Maintenance #{$maintenance->id} terminée");

            // Si une panne est liée, la résoudre
            if ($maintenance->panne_id) {
                $panne = \App\Models\Panne::findOrFail($maintenance->panne_id);
                $panne->update([
                    'statut' => 'resolue',
                    'date_resolution' => now(),
                    'action_realisee' => $data['action_realisee'] ?? $panne->action_realisee,
                    'cout_reparation' => $data['cout'] ?? $panne->cout_reparation,
                ]);
            }

            // Send maintenance completed notification
            $this->sendMaintenanceCompletedNotification($maintenance);

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

    /**
     * Send notification when maintenance is created
     */
    private function sendMaintenanceCreatedNotification(Maintenance $maintenance): void
    {
        $maintenance->load('equipement');
        $equipement = $maintenance->equipement;

        $recipients = \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('name', ['super_admin', 'gestionnaire_stock_general', 'chef_agence', 'gestionnaire_stock', 'technicien_maintenance']))
            ->where(fn($q) => $q->where('agence_id', $equipement->agence_actuelle_id)->orWhereHas('roles', fn($rq) => $rq->whereIn('name', ['super_admin', 'gestionnaire_stock_general'])))
            ->get();

        foreach ($recipients as $recipient) {
            \App\Services\NotificationService::sendNotification(
                user: $recipient,
                type: 'maintenance_programmee',
                title: 'Nouvelle maintenance programmée',
                message: "Une nouvelle {$maintenance->type_maintenance} a été programmée pour l'équipement {$equipement->nom} ({$equipement->reference}) le {$maintenance->date_prevue}.",
                data: ['maintenance_id' => $maintenance->id, 'equipement_id' => $equipement->id],
                channels: ['in_app', 'email']
            );
        }
    }

    /**
     * Send notification when maintenance is completed
     */
    private function sendMaintenanceCompletedNotification(Maintenance $maintenance): void
    {
        $maintenance->load('equipement');
        $equipement = $maintenance->equipement;

        $recipients = \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('name', ['super_admin', 'gestionnaire_stock_general', 'chef_agence', 'gestionnaire_stock']))
            ->where(fn($q) => $q->where('agence_id', $equipement->agence_actuelle_id)->orWhereHas('roles', fn($rq) => $rq->whereIn('name', ['super_admin', 'gestionnaire_stock_general'])))
            ->get();

        foreach ($recipients as $recipient) {
            \App\Services\NotificationService::sendNotification(
                user: $recipient,
                type: 'maintenance_terminee',
                title: 'Maintenance terminée',
                message: "La {$maintenance->type_maintenance} pour l'équipement {$equipement->nom} ({$equipement->reference}) a été terminée.",
                data: ['maintenance_id' => $maintenance->id, 'equipement_id' => $equipement->id],
                channels: ['in_app', 'email']
            );
        }
    }
}
