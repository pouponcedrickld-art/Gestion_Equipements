<?php

namespace App\Http\Controllers;

use App\Models\Consommable;
use App\Models\Equipement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ConsommableController extends Controller
{
    /**
     * Lister tous les consommables avec pagination et recherche
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Consommable::query()->with(['equipement:id,reference,marque,modele']);

            // Recherche par nom
            if ($request->filled('search')) {
                $query->search($request->search);
            }

            // Filtrer par type de consommable
            if ($request->filled('type')) {
                $query->byType($request->type);
            }

            // Filtrer par équipement
            if ($request->filled('equipement_id')) {
                $query->forEquipement($request->equipement_id);
            }

            // Filtrer par stock faible
            if ($request->boolean('stock_faible_only')) {
                $seuil = $request->input('seuil_stock', 1);
                $query->stockFaible($seuil);
            }

            // Tri
            $sortBy = $request->input('sort_by', 'nom');
            $sortOrder = $request->input('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = min($request->input('per_page', 15), 50); // Max 50 par page
            $consommables = $query->paginate($perPage);

            // Ajouter des informations supplémentaires
            $consommables->getCollection()->transform(function ($consommable) {
                $consommable->statut_stock = $consommable->statut_stock;
                $consommable->is_stock_faible = $consommable->isStockFaible();
                return $consommable;
            });

            return response()->json([
                'success' => true,
                'data' => $consommables,
                'message' => 'Consommables récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des consommables',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Créer un nouveau consommable
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validation des données
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'type' => 'required|string|in:batterie,chargeur,cable,protection,accessoire,consommable',
                'equipement_id' => 'required|exists:equipements,id',
                'quantite' => 'required|integer|min:0',
            ], [
                'nom.required' => 'Le nom du consommable est obligatoire',
                'nom.max' => 'Le nom ne peut pas dépasser 255 caractères',
                'type.required' => 'Le type de consommable est obligatoire',
                'type.in' => 'Type de consommable invalide',
                'equipement_id.required' => 'L\'équipement est obligatoire',
                'equipement_id.exists' => 'L\'équipement sélectionné n\'existe pas',
                'quantite.required' => 'La quantité est obligatoire',
                'quantite.integer' => 'La quantité doit être un nombre entier',
                'quantite.min' => 'La quantité ne peut pas être négative',
            ]);

            // Créer le consommable
            $consommable = Consommable::create($validated);
            
            // Charger les relations pour la réponse
            $consommable->load('equipement:id,reference,marque,modele');

            return response()->json([
                'success' => true,
                'data' => $consommable,
                'message' => 'Consommable créé avec succès'
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
                'message' => 'Erreur lors de la création du consommable',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Afficher un consommable
     * 
     * @param Consommable $consommable
     * @return JsonResponse
     */
    public function show(Consommable $consommable): JsonResponse
    {
        try {
            // Charger les relations
            $consommable->load('equipement:id,reference,marque,modele,agence_actuelle_id,statut_global');
            
            // Ajouter des informations calculées
            $consommable->statut_stock = $consommable->statut_stock;
            $consommable->is_stock_faible = $consommable->isStockFaible();

            return response()->json([
                'success' => true,
                'data' => $consommable,
                'message' => 'Consommable récupéré avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du consommable',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Modifier un consommable
     * 
     * @param Request $request
     * @param Consommable $consommable
     * @return JsonResponse
     */
    public function update(Request $request, Consommable $consommable): JsonResponse
    {
        try {
            // Validation des données
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'type' => 'required|string|in:batterie,chargeur,cable,protection,accessoire,consommable',
                'equipement_id' => 'required|exists:equipements,id',
                'quantite' => 'required|integer|min:0',
            ], [
                'nom.required' => 'Le nom du consommable est obligatoire',
                'nom.max' => 'Le nom ne peut pas dépasser 255 caractères',
                'type.required' => 'Le type de consommable est obligatoire',
                'type.in' => 'Type de consommable invalide',
                'equipement_id.required' => 'L\'équipement est obligatoire',
                'equipement_id.exists' => 'L\'équipement sélectionné n\'existe pas',
                'quantite.required' => 'La quantité est obligatoire',
                'quantite.integer' => 'La quantité doit être un nombre entier',
                'quantite.min' => 'La quantité ne peut pas être négative',
            ]);

            // Mettre à jour le consommable
            $consommable->update($validated);
            
            // Recharger avec les relations
            $consommable->load('equipement:id,reference,marque,modele');

            return response()->json([
                'success' => true,
                'data' => $consommable,
                'message' => 'Consommable modifié avec succès'
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
                'message' => 'Erreur lors de la modification du consommable',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Supprimer un consommable
     * 
     * @param Consommable $consommable
     * @return JsonResponse
     */
    public function destroy(Consommable $consommable): JsonResponse
    {
        try {
            $nom = $consommable->nom;
            $consommable->delete();

            return response()->json([
                'success' => true,
                'message' => "Consommable '{$nom}' supprimé avec succès"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du consommable',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Ajuster le stock d'un consommable
     * 
     * @param Request $request
     * @param Consommable $consommable
     * @return JsonResponse
     */
    public function ajusterStock(Request $request, Consommable $consommable): JsonResponse
    {
        try {
            // Validation
            $validated = $request->validate([
                'action' => 'required|string|in:ajouter,retirer',
                'quantite' => 'required|integer|min:1',
                'description' => 'nullable|string|max:500',
            ], [
                'action.required' => 'L\'action est obligatoire',
                'action.in' => 'Action invalide (ajouter ou retirer)',
                'quantite.required' => 'La quantité est obligatoire',
                'quantite.integer' => 'La quantité doit être un nombre entier',
                'quantite.min' => 'La quantité doit être positive',
                'description.max' => 'La description ne peut pas dépasser 500 caractères',
            ]);

            // Effectuer l'ajustement selon l'action
            if ($validated['action'] === 'ajouter') {
                $consommable->ajouterStock($validated['quantite'], $validated['description'] ?? null);
                $message = "Stock augmenté de {$validated['quantite']} unités";
            } else {
                $consommable->retirerStock($validated['quantite'], $validated['description'] ?? null);
                $message = "Stock diminué de {$validated['quantite']} unités";
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'consommable' => $consommable->fresh(),
                    'nouveau_stock' => $consommable->quantite,
                    'statut_stock' => $consommable->statut_stock
                ],
                'message' => $message
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
                'message' => 'Erreur lors de l\'ajustement du stock',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtenir les types de consommables disponibles
     * 
     * @return JsonResponse
     */
    public function getTypes(): JsonResponse
    {
        try {
            $types = Consommable::getTypesDisponibles();

            return response()->json([
                'success' => true,
                'data' => $types,
                'message' => 'Types de consommables récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des types',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des consommables
     * 
     * @return JsonResponse
     */
    public function statistiques(): JsonResponse
    {
        try {
            $stats = [
                'total_consommables' => Consommable::count(),
                'stock_total' => Consommable::sum('quantite'),
                'par_type' => Consommable::selectRaw('type, COUNT(*) as nombre, SUM(quantite) as stock_total')
                    ->groupBy('type')
                    ->get(),
                'stock_faible' => Consommable::stockFaible()->count(),
                'rupture_stock' => Consommable::where('quantite', 0)->count(),
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
}
