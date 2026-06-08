<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Agence\DashboardAgenceController;
use App\Http\Controllers\Direction\UserController;
use App\Http\Controllers\Direction\AgenceController;
use App\Http\Controllers\Agence\AgentController;
use App\Http\Controllers\Direction\EquipementController;
use App\Http\Controllers\Direction\CategorieController;
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
        Route::apiResource('agents', AgentController::class);
    });

    // Équipements
    Route::get('equipements', [EquipementController::class, 'index'])->withoutMiddleware(['agence.scope']);
    Route::apiResource('equipements', EquipementController::class)->except(['index']);
    Route::post('equipements/import', [EquipementController::class, 'import'])->middleware('role:super_admin|gestionnaire_stock_general');
    Route::post('equipements/{id}/qr', [EquipementController::class, 'generateQr'])->middleware('role:super_admin|gestionnaire_stock_general');

    // Catégories
    Route::apiResource('categories', CategorieController::class)->middleware('role:super_admin|gestionnaire_stock_general');

    // Transferts
    Route::get('transferts-kanban', [TransfertController::class, 'index']);
    Route::post('transferts-kanban/update-status', [TransfertController::class, 'updateStatus']);
    Route::apiResource('transferts', TransfertController::class);
    Route::post('transferts/{id}/approuver', [TransfertController::class, 'approuver'])->middleware('role:super_admin|gestionnaire_stock_general');
    Route::post('transferts/{id}/expedier', [TransfertController::class, 'expedier'])->middleware('role:super_admin|gestionnaire_stock_general');
    Route::post('transferts/{id}/recevoir', [TransfertController::class, 'recevoir'])->middleware('role:gestionnaire_stock');

    // Demandes Matériel (Agence)
    Route::apiResource('demandes-materiel', DemandeMaterielController::class);
    
    // Traitement Demandes (Direction)
    Route::post('demandes-materiel/{id}/traiter', [DemandeAgenceController::class, 'traiter'])->middleware('role:super_admin|gestionnaire_stock_general');

    // Affectations
    Route::apiResource('affectations', AffectationController::class);
    Route::post('affectations/{id}/retour', [AffectationController::class, 'retour']);

    // Mouvements
    Route::get('mouvements', [MouvementController::class, 'index']);

    // Pannes
    Route::apiResource('pannes', PanneController::class);
    Route::post('pannes/{id}/transmettre-maintenance', [PanneController::class, 'transmettreMaintenance'])->middleware('role:gestionnaire_stock');
    Route::post('pannes/{id}/diagnostiquer', [PanneController::class, 'diagnostiquer'])->middleware('role:technicien_maintenance|super_admin');

    // Maintenances
    Route::apiResource('maintenances', MaintenanceController::class)->middleware('role:super_admin|gestionnaire_stock_general|technicien_maintenance|gestionnaire_stock');

    // Pertes
    Route::apiResource('pertes', PerteController::class);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::patch('notifications/{id}/lu', [NotificationController::class, 'markAsRead']);

    // Rapports
    Route::middleware('role:super_admin|gestionnaire_stock_general|chef_agence|gestionnaire_stock|technicien_maintenance')->group(function () {
        Route::get('rapports/inventaire', [RapportGlobalController::class, 'inventaire']);
        Route::get('rapports/pannes', [RapportGlobalController::class, 'pannes']);
        Route::get('rapports/export/{type}', [RapportGlobalController::class, 'export']);
    });
});
