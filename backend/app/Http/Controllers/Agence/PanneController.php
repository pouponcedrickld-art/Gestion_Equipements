<?php

namespace App\Http\Controllers\Agence;

use App\Http\Controllers\Controller;
use App\Models\Equipement;
use App\Models\Panne;
use App\Services\PanneWorkflowService;
use App\Services\PanneDecisionService;
use App\Http\Requests\Agence\DiagnostiquerPanneRequest;
use App\Http\Requests\Agence\StorePanneRequest;
use App\Http\Requests\Agence\TransmettreMaintenanceRequest;
use App\Http\Requests\Agence\DeciderPanneRequest;

use App\Http\Resources\PanneResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class PanneController extends Controller
{
    public function __construct(
        private readonly PanneWorkflowService $panneWorkflowService,
        private readonly PanneDecisionService $panneDecisionService
    ) {
    }


    /**
     * Lister les pannes (scope multi-agences via equipement->agence_actuelle_id).
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Panne::query()->with([
            'equipement',
            'agent',
            'gestionnaireStock',
            'technicien',
            'maintenances',
        ]);

        // Filtrage agence (si pas super_admin / rôle direction globale)
        if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance'])) {
            $query->whereHas('equipement', function ($q) use ($user) {
                $q->where('agence_actuelle_id', $user->agence_id);
            });
        }

        // Filtres optionnels
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('niveau_gravite')) {
            $query->where('niveau_gravite', $request->niveau_gravite);
        }

        return response()->json(
            $query->orderBy('date_declaration', 'desc')->paginate(20)
        );
    }

    /**
     * Déclarer une panne.
     */
    public function store(StorePanneRequest $request)
    {


        $user = Auth::user();

        // Vérification scope multi-agences : l'équipement doit être dans l'agence courante de l'utilisateur.
        $equipement = Equipement::query()->findOrFail($request->equipement_id);
        if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance']) && $equipement->agence_actuelle_id !== $user->agence_id) {
            return response()->json(['message' => 'Accès refusé: équipement hors de votre agence'], 403);
        }

        $payload = [
            'description' => $request->description,
            'niveau_gravite' => $request->niveau_gravite,
            'photos' => $request->photos ?? [],
        ];


        $createdPanne = null;

        DB::transaction(function () use (&$createdPanne, $request, $user, $payload, $equipement) {
            // Crée d’abord la panne en mode "declaree" (statut normalisé)
            $createdPanne = Panne::create([

                'equipement_id' => $request->equipement_id,
                'agent_id' => $request->agent_id,
                'date_declaration' => now(),
                'description' => $request->description,
                'niveau_gravite' => $request->niveau_gravite,
                'photos' => $request->photos ?? [],
                'statut' => PanneWorkflowService::STATUT_DECLAREE,
                'gestionnaire_stock_id' => $user->id,
            ]);

            // Applique les règles workflow via service
            $this->panneWorkflowService->declarer($createdPanne, $user, [

                'commentaire' => $panne->description,
            ]);

            // Tracabilité "mouvement" (gérée en partie par le projet existant)
            $equipement->createMouvement(
                'panne',
                "Panne déclarée par agent ID: {$request->agent_id}. Gravité: {$request->niveau_gravite}. Description: {$request->description}",
                $user->id
            );
        });

        $panne->load(['equipement', 'agent', 'gestionnaireStock', 'technicien', 'maintenances', 'statusHistories']);

        return response()->json([
            'message' => 'Panne déclarée avec succès',
            'data' => new PanneResource($panne),
        ], 201);
    }


    /**
     * Détails d’une panne.
     */
    public function show(Panne $panne)
    {
        $user = Auth::user();

        // Scope multi-agences
        if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance']) && $panne->equipement?->agence_actuelle_id !== $user->agence_id) {
            return response()->json(['message' => 'Accès refusé: panne hors de votre agence'], 403);
        }

        return response()->json(
            new PanneResource(
                $panne->load(['equipement', 'agent', 'gestionnaireStock', 'technicien', 'maintenances', 'statusHistories'])
            )
        );

    }

    /**
     * Update générique (legacy).
     * Pour respecter ton cahier des charges, l’API doit privilégier les endpoints de transition,
     * mais on supporte encore update pour compatibilité.
     */
    public function update(Request $request, Panne $panne)
    {

        $request->validate([
            'description' => 'nullable|string',
            'niveau_gravite' => 'nullable|in:mineure,majeure,critique',
            // statuts normalisés
            'statut' => 'nullable|in:declaree,en_cours,en_maintenance,resolue,irrecuperable',
            'technicien_id' => 'nullable|exists:users,id',
            'diagnostic_technicien' => 'nullable|string',
            'action_realisee' => 'nullable|string',
            'cout_reparation' => 'nullable|numeric|min:0',
            'date_resolution' => 'nullable|date',
            'decision_finale' => 'nullable|string',
        ]);

        $user = Auth::user();

        if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance']) && $panne->equipement?->agence_actuelle_id !== $user->agence_id) {
            return response()->json(['message' => 'Accès refusé: panne hors de votre agence'], 403);
        }

        return DB::transaction(function () use ($request, $panne, $user) {
            $payload = $request->only([
                'diagnostic_technicien',
                'action_realisee',
                'cout_reparation',
                'decision_finale',
                'solution',
            ]);

            // Si un statut est fourni : on applique la transition via service.
            if ($request->filled('statut')) {
                $nouveauStatut = $request->input('statut');

                $panneFresh = Panne::query()->whereKey($panne->id)->with('equipement')->firstOrFail();

                match ($nouveauStatut) {
                    PanneWorkflowService::STATUT_EN_COURS => $this->panneWorkflowService->passerEnCours($panneFresh, $user, $payload),
                    PanneWorkflowService::STATUT_EN_MAINTENANCE => $this->panneWorkflowService->passerEnMaintenance($panneFresh, $user, $payload),
                    PanneWorkflowService::STATUT_RESOLUE => $this->panneWorkflowService->resoudre($panneFresh, $user, $payload),
                    PanneWorkflowService::STATUT_IRRECUPERABLE => $this->panneWorkflowService->marquerIrrecuperable($panneFresh, $user, $payload),
                    default => null,
                };

                // Mise à jour des champs non workflow (description/technicien/diagnostic/etc.)
                $panneFresh->fill($request->except(['statut']));
                $panneFresh->save();

                return response()->json(['message' => 'Panne mise à jour avec succès']);
            }

            // Legacy: simple update sans transition
            $panne->update($request->except(['statut']));

            return response()->json(['message' => 'Panne mise à jour avec succès']);
        });
    }

    /**
     * Endpoint dédié: transmettre au technicien.
     * (déplace l’état vers en_cours)
     */
    public function transmettreMaintenance(TransmettreMaintenanceRequest $request, $id)
    {


        $user = Auth::user();
        $panne = Panne::query()->with('equipement')->findOrFail($id);

        if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance']) && $panne->equipement?->agence_actuelle_id !== $user->agence_id) {
            return response()->json(['message' => 'Accès refusé: panne hors de votre agence'], 403);
        }

        $this->panneWorkflowService->passerEnCours($panne, $user, []);

        $panne->update([
            'technicien_id' => $request->technicien_id,
        ]);

        $panne->load(['equipement', 'agent', 'gestionnaireStock', 'technicien', 'maintenances', 'statusHistories']);

        return response()->json([
            'message' => 'Panne transmise au technicien',
            'data' => new PanneResource($panne),
        ]);
    }


    /**
     * Endpoint dédié: diagnostic technicien.
     */
    public function diagnostiquer(DiagnostiquerPanneRequest $request, $id)
    {


        $user = Auth::user();
        $panne = Panne::query()->with('equipement')->findOrFail($id);

        if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance']) && $panne->equipement?->agence_actuelle_id !== $user->agence_id) {
            return response()->json(['message' => 'Accès refusé: panne hors de votre agence'], 403);
        }

        $panne->update([
            'diagnostic_technicien' => $request->diagnostic_technicien,
        ]);

        $panne->load(['equipement', 'agent', 'gestionnaireStock', 'technicien', 'maintenances', 'statusHistories']);

        return response()->json([
            'message' => 'Diagnostic enregistré',
            'data' => new PanneResource($panne),
        ]);
    }

    /**
     * Décision finale technicien (réparer / remplacement / irrécupérable).
     */
    public function decider(DeciderPanneRequest $request, $id)
    {
        $user = Auth::user();

        $panne = Panne::query()->with('equipement')->findOrFail($id);

        if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance']) && $panne->equipement?->agence_actuelle_id !== $user->agence_id) {
            return response()->json(['message' => 'Accès refusé: panne hors de votre agence'], 403);
        }

        $decision = $request->decision;

        $payload = $request->only([
            'cout_estimatif',
            'commentaires',
            'diagnostic_technicien',
            'action_realisee',
            'solution',
        ]);

        $panneUpdated = match ($decision) {
            'reparee' => $this->panneDecisionService->repare($panne, $user, $payload),
            'remplacement' => $this->panneDecisionService->remplacement($panne, $user, $payload),
            'irrecuperable' => $this->panneDecisionService->irrecuperable($panne, $user, $payload),
            default => $panne,
        };

        $panneUpdated->load(['equipement', 'agent', 'gestionnaireStock', 'technicien', 'maintenances', 'statusHistories']);

        return response()->json([
            'message' => 'Décision enregistrée',
            'data' => new PanneResource($panneUpdated),
        ]);
    }


    /**
     * Supprimer (à éviter en production si historique obligatoire).
     */
    public function destroy(Panne $panne)

    {
        $user = Auth::user();

        if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance']) && $panne->equipement?->agence_actuelle_id !== $user->agence_id) {
            return response()->json(['message' => 'Accès refusé: panne hors de votre agence'], 403);
        }

        $panne->delete();

        return response()->json(['message' => 'Panne supprimée avec succès']);
    }
}

