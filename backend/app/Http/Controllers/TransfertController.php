<?php

namespace App\Http\Controllers;

use App\Models\Transfert;
use App\Models\Equipement;
use App\Models\Agence;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class TransfertController extends Controller
{
    /**
     * Lister tous les transferts avec pagination et filtres
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = Transfert::query()->with([
                'equipement:id,reference,marque,modele',
                'agenceSource:id,nom,ville',
                'agenceDestination:id,nom,ville',
                'demandePar:id,name',
                'validePar:id,name'
            ]);

            // Scoper selon le rôle et l'agence
            if ($user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                // Voir tous les transferts
            } elseif ($user->hasRole(['chef_agence', 'gestionnaire_stock'])) {
                // Voir seulement les transferts impliquant son agence
                $query->forAgence($user->agence_id);
            } else {
                // Pas d'accès aux transferts pour les autres rôles
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé aux transferts'
                ], 403);
            }

            // Filtres
            if ($request->filled('statut')) {
                $query->byStatut($request->statut);
            }

            if ($request->filled('type_transfert')) {
                $query->byType($request->type_transfert);
            }

            if ($request->filled('agence_source_id')) {
                $query->where('agence_source_id', $request->agence_source_id);
            }

            if ($request->filled('agence_destination_id')) {
                $query->where('agence_destination_id', $request->agence_destination_id);
            }

            // Filtres par direction (pour l'agence de l'utilisateur)
            if ($request->filled('direction')) {
                $direction = $request->direction;
                if ($direction === 'entrants') {
                    $query->entrants($user->agence_id);
                } elseif ($direction === 'sortants') {
                    $query->sortants($user->agence_id);
                }
            }

            // Filtres par plage de dates
            if ($request->filled('date_from')) {
                $query->whereDate('date_demande', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('date_demande', '<=', $request->date_to);
            }

            // Tri
            $sortBy = $request->input('sort_by', 'date_demande');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = min($request->input('per_page', 15), 50);
            $transferts = $query->paginate($perPage);

            // Ajouter des informations calculées
            $transferts->getCollection()->transform(function ($transfert) {
                $transfert->can_be_modified = $transfert->canBeModified();
                $transfert->can_be_cancelled = $transfert->canBeCancelled();
                $transfert->duree_transfert = $transfert->duree_transfert;
                return $transfert;
            });

            return response()->json([
                'success' => true,
                'data' => $transferts,
                'message' => 'Transferts récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des transferts',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Créer une demande de transfert
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Validation des données
            $validated = $request->validate([
                'equipement_id' => 'required|exists:equipements,id',
                'agence_destination_id' => 'required|exists:agences,id',
                'type_transfert' => 'required|string|in:livraison_generale,retour_generale,transfert_interne',
                'observations' => 'nullable|string|max:1000',
            ], [
                'equipement_id.required' => 'L\'équipement est obligatoire',
                'equipement_id.exists' => 'L\'équipement sélectionné n\'existe pas',
                'agence_destination_id.required' => 'L\'agence de destination est obligatoire',
                'agence_destination_id.exists' => 'L\'agence de destination n\'existe pas',
                'type_transfert.required' => 'Le type de transfert est obligatoire',
                'type_transfert.in' => 'Type de transfert invalide',
                'observations.max' => 'Les observations ne peuvent pas dépasser 1000 caractères',
            ]);

            // Vérifications métier
            $equipement = Equipement::findOrFail($validated['equipement_id']);
            $agenceDestination = Agence::findOrFail($validated['agence_destination_id']);

            // Vérifier que l'équipement est disponible pour transfert
            if (!in_array($equipement->statut_global, ['en_stock_general', 'en_stock_local'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet équipement n\'est pas disponible pour transfert',
                    'data' => ['statut_actuel' => $equipement->statut_global]
                ], 400);
            }

            // Vérifier qu'il n'y a pas déjà un transfert en cours pour cet équipement
            if ($equipement->transferts()->whereIn('statut', ['demande', 'approuve', 'expedie'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet équipement fait déjà l\'objet d\'un transfert en cours'
                ], 400);
            }

            // Déterminer l'agence source selon le contexte
            $agenceSource = $equipement->agenceActuelle;

            // Vérifier les permissions pour la création
            $canCreate = false;
            if ($user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                $canCreate = true;
            } elseif ($user->hasRole('chef_agence')) {
                // Un chef d'agence peut demander des équipements depuis l'agence générale
                $canCreate = ($validated['type_transfert'] === 'livraison_generale' || 
                             $agenceSource->id === $user->agence_id);
            } elseif ($user->hasRole('gestionnaire_stock')) {
                // Un gestionnaire stock peut faire des retours ou transferts internes
                $canCreate = ($validated['type_transfert'] !== 'livraison_generale' && 
                             $agenceSource->id === $user->agence_id);
            }

            if (!$canCreate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas l\'autorisation de créer ce type de transfert'
                ], 403);
            }

            // Créer le transfert
            $transfertData = array_merge($validated, [
                'agence_source_id' => $agenceSource->id,
                'demande_par_id' => $user->id,
                'date_demande' => now(),
                'statut' => 'demande',
                'quantite' => 1, // Pour l'instant, un équipement = une quantité
            ]);

            $transfert = Transfert::create($transfertData);

            // Créer un mouvement pour l'équipement
            $equipement->createMouvement(
                'transfert_demande',
                "Demande de transfert vers {$agenceDestination->nom}",
                $user->id
            );

            // Charger les relations pour la réponse
            $transfert->load([
                'equipement', 
                'agenceSource', 
                'agenceDestination', 
                'demandePar'
            ]);

            return response()->json([
                'success' => true,
                'data' => $transfert,
                'message' => 'Demande de transfert créée avec succès'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la demande de transfert',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Afficher un transfert
     * 
     * @param Transfert $transfert
     * @return JsonResponse
     */
    public function show(Transfert $transfert): JsonResponse
    {
        try {
            // Charger toutes les relations
            $transfert->load([
                'equipement.categorie',
                'agenceSource',
                'agenceDestination', 
                'demandePar',
                'validePar'
            ]);

            // Ajouter des informations calculées
            $transfert->can_be_modified = $transfert->canBeModified();
            $transfert->can_be_cancelled = $transfert->canBeCancelled();
            $transfert->duree_transfert = $transfert->duree_transfert;

            return response()->json([
                'success' => true,
                'data' => $transfert,
                'message' => 'Transfert récupéré avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du transfert',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Approuver un transfert (Gestionnaire Stock Général uniquement)
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function approuver(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $transfert = Transfert::findOrFail($id);

            // Vérifier les permissions
            if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas l\'autorisation d\'approuver les transferts'
                ], 403);
            }

            // Approuver le transfert
            $transfert->approuver($user->id);

            // Créer un mouvement pour l'équipement
            $transfert->equipement->createMouvement(
                'transfert_approuve',
                "Transfert approuvé par {$user->name}",
                $user->id
            );

            $transfert->load(['equipement', 'agenceSource', 'agenceDestination', 'validePar']);

            return response()->json([
                'success' => true,
                'data' => $transfert,
                'message' => 'Transfert approuvé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 400);
        }
    }

    /**
     * Refuser un transfert (Gestionnaire Stock Général uniquement)
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function refuser(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $transfert = Transfert::findOrFail($id);

            // Validation
            $validated = $request->validate([
                'observations' => 'required|string|max:1000'
            ], [
                'observations.required' => 'Les observations sont obligatoires pour refuser un transfert',
                'observations.max' => 'Les observations ne peuvent pas dépasser 1000 caractères',
            ]);

            // Vérifier les permissions
            if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas l\'autorisation de refuser les transferts'
                ], 403);
            }

            // Refuser le transfert
            $transfert->refuser($user->id, $validated['observations']);

            // Créer un mouvement pour l'équipement
            $transfert->equipement->createMouvement(
                'transfert_refuse',
                "Transfert refusé par {$user->name}: {$validated['observations']}",
                $user->id
            );

            $transfert->load(['equipement', 'agenceSource', 'agenceDestination', 'validePar']);

            return response()->json([
                'success' => true,
                'data' => $transfert,
                'message' => 'Transfert refusé'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 400);
        }
    }

    /**
     * Expédier un transfert approuvé
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function expedier(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $transfert = Transfert::findOrFail($id);

            // Vérifier les permissions
            if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas l\'autorisation d\'expédier les transferts'
                ], 403);
            }

            DB::beginTransaction();
            
            try {
                // Expédier le transfert
                $transfert->expedier($user->id);

                DB::commit();

                $transfert->load(['equipement', 'agenceSource', 'agenceDestination']);

                return response()->json([
                    'success' => true,
                    'data' => $transfert,
                    'message' => 'Transfert expédié avec succès'
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 400);
        }
    }

    /**
     * Recevoir un transfert expédié
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function recevoir(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $transfert = Transfert::findOrFail($id);

            // Vérifier que l'utilisateur est de l'agence de destination
            if ($user->agence_id !== $transfert->agence_destination_id && 
                !$user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez recevoir que les transferts destinés à votre agence'
                ], 403);
            }

            // Vérifier les permissions
            if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general', 'gestionnaire_stock'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas l\'autorisation de recevoir les transferts'
                ], 403);
            }

            DB::beginTransaction();
            
            try {
                // Recevoir le transfert
                $transfert->recevoir($user->id);

                DB::commit();

                $transfert->load(['equipement', 'agenceSource', 'agenceDestination']);

                return response()->json([
                    'success' => true,
                    'data' => $transfert,
                    'message' => 'Transfert reçu avec succès'
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 400);
        }
    }

    /**
     * Obtenir les statistiques des transferts
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function statistiques(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = Transfert::query();

            // Scoper selon le rôle
            if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                $query->forAgence($user->agence_id);
            }

            $stats = [
                'total' => (clone $query)->count(),
                'par_statut' => (clone $query)->selectRaw('statut, COUNT(*) as count')
                    ->groupBy('statut')->pluck('count', 'statut'),
                'par_type' => (clone $query)->selectRaw('type_transfert, COUNT(*) as count')
                    ->groupBy('type_transfert')->pluck('count', 'type_transfert'),
                'en_attente_approbation' => (clone $query)->byStatut('demande')->count(),
                'en_transit' => (clone $query)->byStatut('expedie')->count(),
                'termines_ce_mois' => (clone $query)->whereIn('statut', ['recu', 'refuse'])
                    ->whereMonth('updated_at', now()->month)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtenir les statuts et types disponibles
     * 
     * @return JsonResponse
     */
    public function getOptions(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'statuts' => Transfert::getStatusDisponibles(),
                    'types' => Transfert::getTypesDisponibles(),
                ],
                'message' => 'Options récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des options',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
