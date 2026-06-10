<?php

namespace App\Http\Controllers\Agence;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaintenanceRequest;
use App\Models\Maintenance;
use App\Services\MaintenanceWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MaintenanceController extends Controller
{
    public function __construct(
        private MaintenanceWorkflowService $workflowService
    ) {}

    /**
     * Liste des maintenances avec filtres
     * Query params: start_date, end_date, month, type_maintenance, statut
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Vérifier l'autorisation
        Gate::authorize('viewAny', Maintenance::class);

        // Cas 1: Filtrage par mois (month parameter)
        if ($request->has('month')) {
            $month = $request->input('month');
            
            // Valider le format YYYY-MM
            if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le format du mois doit être YYYY-MM',
                    'errors' => ['month' => ['Format invalide']]
                ], 422);
            }

            // Convertir le mois en plage de dates
            [$year, $monthNum] = explode('-', $month);
            $startDate = "{$year}-{$monthNum}-01";
            $endDate = date('Y-m-t', strtotime($startDate)); // Dernier jour du mois
        } 
        // Cas 2: Filtrage par plage de dates explicite
        elseif ($request->has('start_date') && $request->has('end_date')) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // Valider les dates
            if (!strtotime($startDate) || !strtotime($endDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dates invalides',
                    'errors' => ['dates' => ['Les dates doivent être valides']]
                ], 422);
            }
        } 
        // Cas 3: Aucun filtre de date - retourner le mois en cours
        else {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        }

        // Préparer les filtres additionnels
        $filters = [];
        if ($request->has('type_maintenance') && $request->input('type_maintenance') !== '') {
            $filters['type_maintenance'] = $request->input('type_maintenance');
        }
        if ($request->has('statut') && $request->input('statut') !== '') {
            $filters['statut'] = $request->input('statut');
        }

        // Récupérer les maintenances via le service
        $maintenances = $this->workflowService->getByPeriod($startDate, $endDate, $filters);

        // Pagination manuelle (limite à 100 par page)
        $perPage = 100;
        $currentPage = $request->input('page', 1);
        $total = $maintenances->count();
        $paginatedMaintenances = $maintenances->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return response()->json([
            'success' => true,
            'data' => $paginatedMaintenances,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => (int) $currentPage,
                'last_page' => (int) ceil($total / $perPage),
            ]
        ], 200);
    }

    /**
     * Détails d'une maintenance avec relations eager-loaded
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            // Récupérer la maintenance avec relations via le service
            $maintenance = $this->workflowService->getMaintenanceWithRelations($id);

            // Vérifier l'autorisation
            Gate::authorize('view', $maintenance);

            return response()->json([
                'success' => true,
                'data' => $maintenance
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Maintenance non trouvée'
            ], 404);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'avez pas les permissions pour accéder à cette maintenance.'
            ], 403);
        }
    }

    /**
     * Créer une maintenance préventive
     * 
     * @param MaintenanceRequest $request
     * @return JsonResponse
     */
    public function store(MaintenanceRequest $request): JsonResponse
    {
        // Vérifier l'autorisation de planifier
        Gate::authorize('planifier', Maintenance::class);

        // Créer la maintenance via le service
        $maintenance = $this->workflowService->planifierPreventive($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Maintenance créée avec succès',
            'data' => $maintenance
        ]);
    }
}
