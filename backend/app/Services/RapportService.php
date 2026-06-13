<?php

namespace App\Services;

use App\Models\Agence;
use App\Models\Equipement;
use App\Models\Maintenance;
use App\Models\Panne;
use App\Models\PerteCas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;

class RapportService
{
    /**
     * Get inventory by agency
     */
    public function getInventaireParAgence(int $agenceId, array $filters = []): array
    {
        $agence = Agence::with(['equipements.categorie', 'equipements.user'])->findOrFail($agenceId);
        $equipements = $agence->equipements();

        if (isset($filters['categorie_id'])) {
            $equipements->where('categorie_id', $filters['categorie_id']);
        }
        if (isset($filters['statut_global'])) {
            $equipements->where('statut_global', $filters['statut_global']);
        }

        $equipements = $equipements->get();

        return [
            'agence' => $agence,
            'equipements' => $equipements,
            'total' => $equipements->count(),
            'date' => Carbon::now()->format('d/m/Y H:i')
        ];
    }

    /**
     * Get assigned equipment
     */
    public function getEquipementsAffectes(array $filters = []): array
    {
        $query = Equipement::whereNotNull('user_id')
            ->with(['user', 'categorie', 'agenceActuelle']);

        if (isset($filters['agence_id'])) {
            $query->where('agence_actuelle_id', $filters['agence_id']);
        }
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        $equipements = $query->get();

        return [
            'equipements' => $equipements,
            'total' => $equipements->count(),
            'date' => Carbon::now()->format('d/m/Y H:i')
        ];
    }

    /**
     * Get equipment in breakdown
     */
    public function getEquipementsEnPanne(array $filters = []): array
    {
        $query = Panne::where('statut', '!=', 'resolue')
            ->where('statut', '!=', 'cloturee')
            ->with(['equipement.categorie', 'equipement.agenceActuelle', 'user']);

        if (isset($filters['agence_id'])) {
            $query->whereHas('equipement', function($q) use ($filters) {
                $q->where('agence_actuelle_id', $filters['agence_id']);
            });
        }
        if (isset($filters['date_debut']) && isset($filters['date_fin'])) {
            $query->whereBetween('created_at', [$filters['date_debut'], $filters['date_fin']]);
        }

        $pannes = $query->get();

        return [
            'pannes' => $pannes,
            'total' => $pannes->count(),
            'date' => Carbon::now()->format('d/m/Y H:i')
        ];
    }

    /**
     * Get maintenances
     */
    public function getMaintenances(array $filters = []): array
    {
        $query = Maintenance::with(['equipement.categorie', 'technicienUser', 'panne']);

        if (isset($filters['agence_id'])) {
            $query->whereHas('equipement', function($q) use ($filters) {
                $q->where('agence_actuelle_id', $filters['agence_id']);
            });
        }
        if (isset($filters['type_maintenance'])) {
            $query->where('type_maintenance', $filters['type_maintenance']);
        }
        if (isset($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }
        if (isset($filters['date_debut']) && isset($filters['date_fin'])) {
            $query->whereBetween('date_prevue', [$filters['date_debut'], $filters['date_fin']]);
        }

        $maintenances = $query->get();

        return [
            'maintenances' => $maintenances,
            'total' => $maintenances->count(),
            'date' => Carbon::now()->format('d/m/Y H:i')
        ];
    }

    /**
     * Get losses and damages
     */
    public function getPertesEtCasses(array $filters = []): array
    {
        $query = PerteCas::with(['equipement.categorie', 'equipement.agenceActuelle', 'user']);

        if (isset($filters['agence_id'])) {
            $query->whereHas('equipement', function($q) use ($filters) {
                $q->where('agence_actuelle_id', $filters['agence_id']);
            });
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (isset($filters['date_debut']) && isset($filters['date_fin'])) {
            $query->whereBetween('date', [$filters['date_debut'], $filters['date_fin']]);
        }

        $pertes = $query->get();

        return [
            'pertes' => $pertes,
            'total' => $pertes->count(),
            'date' => Carbon::now()->format('d/m/Y H:i')
        ];
    }

    /**
     * Get complete audit report
     */
    public function getAuditComplet(int $agenceId = null): array
    {
        $agenceQuery = $agenceId ? Agence::findOrFail($agenceId) : null;

        $inventaire = Equipement::when($agenceId, function($q) use ($agenceId) {
                $q->where('agence_actuelle_id', $agenceId);
            })->count();

        $affectes = Equipement::whereNotNull('user_id')
            ->when($agenceId, function($q) use ($agenceId) {
                $q->where('agence_actuelle_id', $agenceId);
            })->count();

        $pannes = Panne::when($agenceId, function($q) use ($agenceId) {
                $q->whereHas('equipement', function($eq) use ($agenceId) {
                    $eq->where('agence_actuelle_id', $agenceId);
                });
            })->count();

        $pannesResolues = Panne::where('statut', 'resolue')
            ->orWhere('statut', 'cloturee')
            ->when($agenceId, function($q) use ($agenceId) {
                $q->whereHas('equipement', function($eq) use ($agenceId) {
                    $eq->where('agence_actuelle_id', $agenceId);
                });
            })->count();

        $maintenances = Maintenance::when($agenceId, function($q) use ($agenceId) {
                $q->whereHas('equipement', function($eq) use ($agenceId) {
                    $eq->where('agence_actuelle_id', $agenceId);
                });
            })->count();

        $pertes = PerteCas::when($agenceId, function($q) use ($agenceId) {
                $q->whereHas('equipement', function($eq) use ($agenceId) {
                    $eq->where('agence_actuelle_id', $agenceId);
                });
            })->count();

        return [
            'agence' => $agenceQuery,
            'inventaire_total' => $inventaire,
            'equipements_affectes' => $affectes,
            'pannes_total' => $pannes,
            'pannes_resolues' => $pannesResolues,
            'maintenances_total' => $maintenances,
            'pertes_total' => $pertes,
            'date' => Carbon::now()->format('d/m/Y H:i')
        ];
    }

    /**
     * Generate PDF from view and data
     */
    public function genererPDF(string $view, array $data, string $filename)
    {
        $pdf = Pdf::loadView($view, $data);
        return $pdf->download($filename);
    }

    /**
     * Stream PDF preview
     */
    public function previewPDF(string $view, array $data)
    {
        $pdf = Pdf::loadView($view, $data);
        return $pdf->stream();
    }
}
