<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Services\RapportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
     * Exportation des rapports
     */
    public function export(Request $request, $type): JsonResponse
    {
        $filters = $request->only(['agence_id', 'categorie_id', 'statut']);
        $data = $this->rapportService->getEquipementsReport($filters);

        return response()->json([
            'success' => true,
            'data' => $data,
            'type' => $type
        ]);
    }
}
