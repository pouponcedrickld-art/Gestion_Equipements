<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Services\RapportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Exports\EquipementsExport;
use App\Exports\PannesExport;
use App\Exports\MaintenancesExport;
use App\Exports\AffectationsExport;
use App\Exports\MouvementsExport;
use Maatwebsite\Excel\Facades\Excel;

class RapportGlobalController extends Controller
{
    protected $rapportService;

    public function __construct(RapportService $rapportService)
    {
        $this->rapportService = $rapportService;
    }

    /**
     * Statistiques globales pour le dashboard ou rapports généraux
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->rapportService->getGlobalStats()
        ]);
    }

    /**
     * Rapport d'inventaire filtré
     */
    public function inventaire(Request $request): JsonResponse
    {
        $filters = $request->only(['agence_id', 'categorie_id', 'statut']);
        return response()->json([
            'success' => true,
            'data' => $this->rapportService->getEquipementsReport($filters)
        ]);
    }

    /**
     * Statistiques des pannes
     */
    public function pannes(Request $request): JsonResponse
    {
        $filters = $request->only(['statut']);
        return response()->json([
            'success' => true,
            'data' => $this->rapportService->getPannesStats($filters)
        ]);
    }

    /**
     * Statistiques des mouvements
     */
    public function mouvements(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        return response()->json([
            'success' => true,
            'data' => $this->rapportService->getMouvementsStats($period)
        ]);
    }

    /**
     * Exportation Excel
     */
    public function export(Request $request, $type)
    {
        $filters = $request->only(['agence_id', 'categorie_id', 'statut', 'type_maintenance', 'date_debut', 'date_fin']);

        switch ($type) {
            case 'inventaire':
                return Excel::download(new EquipementsExport($filters), 'inventaire.xlsx');
            case 'pannes':
                return Excel::download(new PannesExport($filters), 'pannes.xlsx');
            case 'maintenances':
                return Excel::download(new MaintenancesExport($filters), 'maintenances.xlsx');
            case 'affectations':
                return Excel::download(new AffectationsExport($filters), 'affectations.xlsx');
            case 'mouvements':
                return Excel::download(new MouvementsExport($filters), 'mouvements.xlsx');
            default:
                return response()->json(['error' => 'Type de rapport invalide'], 400);
        }
    }
}
