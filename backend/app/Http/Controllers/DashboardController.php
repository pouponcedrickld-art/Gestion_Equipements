<?php

namespace App\Http\Controllers;

use App\Models\Equipement;
use App\Models\Transfert;
use App\Models\Maintenance;
use App\Models\Panne;
use App\Models\Consommable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $agenceId = $user->agence_id;
            $isGlobal = $user->hasRole(['super_admin', 'gestionnaire_stock_general']);

            // Statistiques Équipements
            $equipementQuery = Equipement::query();
            if (!$isGlobal) {
                $equipementQuery->where('agence_actuelle_id', $agenceId);
            }

            $statsEquipements = [
                'total' => (clone $equipementQuery)->count(),
                'disponibles' => (clone $equipementQuery)->where('statut_global', 'en_stock_general')->orWhere('statut_global', 'en_stock_local')->count(),
                'affectes' => (clone $equipementQuery)->where('statut_global', 'affecte')->count(),
                'en_panne' => (clone $equipementQuery)->where('statut_global', 'en_panne')->count(),
            ];

            // Statistiques Maintenances
            $maintenanceQuery = Maintenance::query();
            if (!$isGlobal) {
                $maintenanceQuery->whereHas('equipement', function($q) use ($agenceId) {
                    $q->where('agence_actuelle_id', $agenceId);
                });
            }

            $statsMaintenances = [
                'planifiees' => (clone $maintenanceQuery)->where('statut', 'planifiee')->count(),
                'en_cours' => (clone $maintenanceQuery)->where('statut', 'en_cours')->count(),
                'terminees_mois' => (clone $maintenanceQuery)->where('statut', 'terminee')->whereMonth('date_fin', now()->month)->count(),
            ];

            // Statistiques Transferts (Alertes)
            $transfertQuery = Transfert::query();
            if (!$isGlobal) {
                $transfertQuery->where(function($q) use ($agenceId) {
                    $q->where('agence_source_id', $agenceId)->orWhere('agence_destination_id', $agenceId);
                });
            }

            $statsTransferts = [
                'attente_approbation' => (clone $transfertQuery)->where('statut', 'demande')->count(),
                'en_transit' => (clone $transfertQuery)->where('statut', 'expedie')->count(),
            ];

            // Consommables (Alertes Stock Faible)
            $alertesConsommables = Consommable::where('quantite', '<=', 5)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'equipements' => $statsEquipements,
                    'maintenances' => $statsMaintenances,
                    'transferts' => $statsTransferts,
                    'alertes_consommables' => $alertesConsommables,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}