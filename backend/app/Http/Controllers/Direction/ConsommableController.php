<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Consommable;
use App\Models\Equipement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ConsommableController extends Controller
{
    /**
     * Lister tous les consommables avec pagination et recherche
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = Consommable::query()->with(['equipement:id,reference,marque,modele']);
            
            // Scope selon rôle
            if ($user->hasRole('agent')) {
                // Agent ne voit pas les consommables (ou peut-être ? Let's check RoleSeeder - agent doesn't have consommable permissions)
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas la permission de voir les consommables.'
                ], 403);
            } elseif (!$user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                // Chef d'agence, gestionnaire local, technicien: voit consommables de son agence
                $query->byAgence($user->agence_id);
            }
            
            // Vérifier la permission de voir
            if (!$user->can('consommables.view_global') && !$user->can('consommables.view_agence')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas la permission de voir les consommables.'
                ], 403);
            }

            if ($request->filled('search')) {
                $query->search($request->search);
            }

            if ($request->filled('type')) {
                $query->byType($request->type);
            }

            if ($request->filled('equipement_id')) {
                $query->forEquipement($request->equipement_id);
            }

            if ($request->boolean('stock_faible_only')) {
                $query->whereColumn('quantite', '<=', 'seuil_alerte');
            }

            $sortBy = $request->input('sort_by', 'nom');
            $sortOrder = $request->input('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = min($request->input('per_page', 15), 50);
            $consommables = $query->paginate($perPage);

            $consommables->getCollection()->transform(function ($consommable) {
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
                'message' => 'Erreur lors de la récupération',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouveau consommable
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Vérifier la permission
            if (!$user->can('consommables.create')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas la permission de créer des consommables.'
                ], 403);
            }
            
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'type' => 'required|string|in:batterie,chargeur,cable,protection,accessoire,consommable',
                'equipement_id' => 'required|exists:equipements,id',
                'quantite' => 'required|integer|min:0',
                'seuil_alerte' => 'nullable|integer|min:0',
            ]);

            $consommable = Consommable::create($validated);
            
            return response()->json([
                'success' => true,
                'data' => $consommable->load('equipement:id,reference,marque,modele'),
                'message' => 'Consommable créé avec succès'
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Afficher un consommable
     */
    public function show(Consommable $consommable): JsonResponse
    {
        $user = request()->user();
        
        // Vérifier l'accès
        if ($user->hasRole('agent')) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'avez pas la permission de voir ce consommable.'
            ], 403);
        } elseif (!$user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
            // Vérifier si le consommable appartient à l'agence de l'utilisateur
            $belongsToAgence = $consommable->equipement()->where('agence_actuelle_id', $user->agence_id)->exists();
            if (!$belongsToAgence) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas la permission de voir ce consommable.'
                ], 403);
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $consommable->load(['equipement', 'mouvements.user'])
        ]);
    }

    /**
     * Modifier un consommable
     */
    public function update(Request $request, Consommable $consommable): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Vérifier la permission
            if (!$user->can('consommables.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas la permission de modifier des consommables.'
                ], 403);
            }
            
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'type' => 'required|string|in:batterie,chargeur,cable,protection,accessoire,consommable',
                'equipement_id' => 'required|exists:equipements,id',
                'quantite' => 'required|integer|min:0',
                'seuil_alerte' => 'nullable|integer|min:0',
            ]);

            $consommable->update($validated);
            
            return response()->json([
                'success' => true,
                'data' => $consommable->load('equipement:id,reference,marque,modele'),
                'message' => 'Consommable modifié avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Supprimer un consommable
     */
    public function destroy(Request $request, Consommable $consommable): JsonResponse
    {
        $user = $request->user();
        
        // Vérifier la permission
        if (!$user->can('consommables.delete')) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'avez pas la permission de supprimer des consommables.'
            ], 403);
        }
        
        $consommable->delete();
        return response()->json(['success' => true, 'message' => 'Consommable supprimé']);
    }

    /**
     * Ajuster le stock
     */
    public function ajusterStock(Request $request, Consommable $consommable): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Vérifier la permission (utiliser edit permission pour ajuster le stock)
            if (!$user->can('consommables.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas la permission d\'ajuster le stock des consommables.'
                ], 403);
            }
            
            $validated = $request->validate([
                'action' => 'required|string|in:ajouter,retirer',
                'quantite' => 'required|integer|min:1',
                'description' => 'nullable|string|max:500',
            ]);

            if ($validated['action'] === 'ajouter') {
                $consommable->ajouterStock($validated['quantite'], $validated['description'] ?? null);
            } else {
                $consommable->retirerStock($validated['quantite'], $validated['description'] ?? null);
            }

            return response()->json([
                'success' => true,
                'data' => $consommable->fresh(),
                'message' => 'Stock mis à jour'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Statistiques
     */
    public function statistiques(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_consommables' => Consommable::count(),
                'stock_total' => Consommable::sum('quantite'),
                'stock_faible' => Consommable::whereColumn('quantite', '<=', 'seuil_alerte')->count(),
            ]
        ]);
    }
}
