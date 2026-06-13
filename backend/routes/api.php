<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Agence\DashboardAgenceController;
use App\Http\Controllers\Direction\UserController;
use App\Http\Controllers\Direction\AgenceController;
use App\Http\Controllers\Agence\AgentController;
use App\Http\Controllers\Direction\EquipementController;
use App\Http\Controllers\Direction\CategorieController;
use App\Http\Controllers\Direction\ConsommableController;
use App\Http\Controllers\Direction\TransfertController;
use App\Http\Controllers\Agence\DemandeMaterielController;
use App\Http\Controllers\Direction\DemandeAgenceController;
use App\Http\Controllers\Agence\AffectationController;
use App\Http\Controllers\Agence\MouvementController;
use App\Http\Controllers\Agence\PanneController;
use App\Http\Controllers\Agence\MaintenanceController;
use App\Http\Controllers\Agence\PerteController;
use App\Http\Controllers\Direction\NotificationController;
use App\Http\Controllers\Direction\RapportGlobalController;
use App\Http\Controllers\Agence\StockAgenceController as AgenceStockController;
use App\Http\Controllers\Direction\StockAgenceController as DirectionStockController;

// ROUTES PUBLIQUES
Route::post('/login', [AuthController::class, 'login']);
Route::post('/2fa/verify', [AuthController::class, 'verify2FA']);

// ROUTES PROTÉGÉES (Auth simple)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// ROUTES PROTÉGÉES (Auth + Scope Agence)
Route::middleware(['auth:sanctum', 'agence.scope'])->group(function () {
    // Dashboard (Cedric)
    Route::get('/dashboard', [DashboardAgenceController::class, 'index']);

    // Agences
    Route::middleware('role:super_admin')->group(function () {
        Route::apiResource('agences', AgenceController::class);
        Route::get('agences/{agence}/stats', [AgenceController::class, 'stats']);
    });

    // Utilisateurs
    Route::apiResource('users', UserController::class);
    Route::post('users/{user}/toggle-actif', [UserController::class, 'toggleActif']);

    // Agents
    Route::middleware('role:super_admin|gestionnaire_stock_general|chef_agence|gestionnaire_stock')->group(function () {
        Route::get('agents/available', [AgentController::class, 'available']);
        Route::get('agents/postes', [AgentController::class, 'postes']);
        Route::apiResource('agents', AgentController::class);
    });

    // Équipements
    Route::get('equipements', [EquipementController::class, 'index'])->withoutMiddleware(['agence.scope']);
    Route::apiResource('equipements', EquipementController::class)->except(['index']);
    Route::post('equipements/import', [EquipementController::class, 'import'])->middleware('role:super_admin|gestionnaire_stock_general');
    Route::get('equipements/import/template', [EquipementController::class, 'downloadTemplate'])->middleware('role:super_admin|gestionnaire_stock_general');
    Route::post('equipements/{id}/qr', [EquipementController::class, 'generateQr'])->middleware('role:super_admin|gestionnaire_stock_general');
    Route::get('equipements/search/advanced', [EquipementController::class, 'search']);

    // Catégories
    Route::get('categories', [CategorieController::class, 'index']);
    Route::get('categories/list', [CategorieController::class, 'list']);
    Route::get('categories/{categorie}', [CategorieController::class, 'show']);
    
    Route::middleware('role:super_admin|gestionnaire_stock_general')->group(function () {
        Route::post('categories', [CategorieController::class, 'store']);
        Route::put('categories/{categorie}', [CategorieController::class, 'update']);
        Route::delete('categories/{categorie}', [CategorieController::class, 'destroy']);
    });

    // Consommables  
    Route::apiResource('consommables', ConsommableController::class);
    Route::post('consommables/{consommable}/ajuster-stock', [ConsommableController::class, 'ajusterStock']);
    Route::get('consommables-types', [ConsommableController::class, 'getTypes']);
    Route::get('consommables/statistiques', [ConsommableController::class, 'statistiques']);

    // Transferts
    Route::get('transferts-kanban', [TransfertController::class, 'index']);
    Route::post('transferts-kanban/update-status', [TransfertController::class, 'updateStatus']);
    Route::get('transferts/demandes-approuvees', [TransfertController::class, 'getApprovedDemandes'])->middleware('role:super_admin|gestionnaire_stock_general');
    Route::post('transferts/creer-depuis-demande/{demandeId}', [TransfertController::class, 'createFromDemande'])->middleware('role:super_admin|gestionnaire_stock_general');
    Route::apiResource('transferts', TransfertController::class);
    Route::post('transferts/{id}/approuver', [TransfertController::class, 'approuver'])->middleware('role:super_admin|gestionnaire_stock_general');
    Route::post('transferts/{id}/refuser', [TransfertController::class, 'refuser'])->middleware('role:super_admin|gestionnaire_stock_general|chef_agence|gestionnaire_stock');
    Route::post('transferts/{id}/expedier', [TransfertController::class, 'expedier'])->middleware('role:super_admin|gestionnaire_stock_general');
    Route::post('transferts/{id}/recevoir', [TransfertController::class, 'recevoir'])->middleware('role:super_admin|gestionnaire_stock|chef_agence');
    Route::get('transferts/statistiques', [TransfertController::class, 'statistiques']);
    Route::get('transferts/options', [TransfertController::class, 'getOptions']);

    // Demandes Matériel (Agence)
    Route::apiResource('demandes-materiel', DemandeMaterielController::class);
    
    // Traitement Demandes (Direction)
    Route::post('demandes-materiel/{id}/traiter', [DemandeAgenceController::class, 'traiter'])->middleware('role:super_admin|gestionnaire_stock_general');

    // Affectations
    Route::apiResource('affectations', AffectationController::class);
    Route::post('affectations/{id}/retour', [AffectationController::class, 'retour']);
    Route::get('mes-affectations', [AffectationController::class, 'mesAffectations']);

    // Mouvements
    Route::get('mouvements', [MouvementController::class, 'index']);

    // Pannes
    Route::apiResource('pannes', PanneController::class);
    Route::post('pannes/{id}/transmettre-maintenance', [PanneController::class, 'transmettreMaintenance'])->middleware('role:gestionnaire_stock');
    Route::post('pannes/{id}/diagnostiquer', [PanneController::class, 'diagnostiquer'])->middleware('role:technicien_maintenance|super_admin');
    Route::post('pannes/{id}/decider', [PanneController::class, 'decider'])->middleware('role:technicien_maintenance|super_admin');
    Route::post('pannes/{id}/update-resultat', [PanneController::class, 'updateResultat'])->middleware('role:technicien_maintenance|super_admin');
    Route::post('pannes/{id}/cloturer', [PanneController::class, 'cloturer'])->middleware('role:technicien_maintenance|super_admin|gestionnaire_stock');

    // Maintenances
    Route::apiResource('maintenances', MaintenanceController::class)->middleware('role:super_admin|gestionnaire_stock_general|technicien_maintenance|gestionnaire_stock');
    Route::post('maintenances/{id}/start', [MaintenanceController::class, 'start'])->middleware('role:super_admin|technicien_maintenance');
    Route::post('maintenances/{id}/complete', [MaintenanceController::class, 'complete'])->middleware('role:super_admin|technicien_maintenance');

    // Pertes
    Route::apiResource('pertes', PerteController::class);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::patch('notifications/{id}/lu', [NotificationController::class, 'markAsRead']);
    Route::patch('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

    // Rapports
    Route::middleware('role:super_admin|gestionnaire_stock_general|chef_agence|gestionnaire_stock|technicien_maintenance')->group(function () {
        Route::get('rapports/global', [RapportGlobalController::class, 'index']);
        Route::get('rapports/inventaire', [RapportGlobalController::class, 'inventaire']);
        Route::get('rapports/pannes', [RapportGlobalController::class, 'pannes']);
        Route::get('rapports/export/{type}', [RapportGlobalController::class, 'export']);

        // Nouveaux rapports PDF
        Route::get('rapports/inventaire-par-agence', [RapportController::class, 'inventaireParAgence']);
        Route::get('rapports/inventaire-par-agence/download', [RapportController::class, 'downloadInventaireParAgence']);
        Route::get('rapports/inventaire-par-agence/preview', [RapportController::class, 'previewInventaireParAgence']);

        Route::get('rapports/equipements-affectes', [RapportController::class, 'equipementsAffectes']);
        Route::get('rapports/equipements-affectes/download', [RapportController::class, 'downloadEquipementsAffectes']);
        Route::get('rapports/equipements-affectes/preview', [RapportController::class, 'previewEquipementsAffectes']);

        Route::get('rapports/equipements-en-panne', [RapportController::class, 'equipementsEnPanne']);
        Route::get('rapports/equipements-en-panne/download', [RapportController::class, 'downloadEquipementsEnPanne']);
        Route::get('rapports/equipements-en-panne/preview', [RapportController::class, 'previewEquipementsEnPanne']);

        Route::get('rapports/maintenances', [RapportController::class, 'maintenances']);
        Route::get('rapports/maintenances/download', [RapportController::class, 'downloadMaintenances']);
        Route::get('rapports/maintenances/preview', [RapportController::class, 'previewMaintenances']);

        Route::get('rapports/pertes-et-casses', [RapportController::class, 'pertesEtCasses']);
        Route::get('rapports/pertes-et-casses/download', [RapportController::class, 'downloadPertesEtCasses']);
        Route::get('rapports/pertes-et-casses/preview', [RapportController::class, 'previewPertesEtCasses']);

        Route::get('rapports/audit-complet', [RapportController::class, 'auditComplet']);
        Route::get('rapports/audit-complet/download', [RapportController::class, 'downloadAuditComplet']);
        Route::get('rapports/audit-complet/preview', [RapportController::class, 'previewAuditComplet']);
    });

    // Stock des Agences
    Route::prefix('stock-agences')->group(function () {
        // Routes pour la direction
        Route::middleware('role:super_admin|gestionnaire_stock_general')->group(function () {
            Route::get('/', [DirectionStockController::class, 'index']);
            Route::get('/agence/{agenceId}', [DirectionStockController::class, 'showByAgence']);
        });

        // Routes pour l'agence
        Route::middleware('role:chef_agence|gestionnaire_stock')->group(function () {
            Route::get('/mon-stock', [AgenceStockController::class, 'index']);
            Route::get('/mon-stock/{id}', [AgenceStockController::class, 'show']);
        });
    });
});
