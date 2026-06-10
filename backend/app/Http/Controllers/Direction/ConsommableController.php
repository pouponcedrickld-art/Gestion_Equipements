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
            $query = Consommable::query()->with(['equipement:id,reference,marque,modele']);

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
        return response()->json([
            'success' => true,
            'data' => $consommable->load('equipement')
        ]);
    }

    /**
     * Modifier un consommable
     */
    public function update(Request $request, Consommable $consommable): JsonResponse
    {
        try {
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
    public function destroy(Consommable $consommable): JsonResponse
    {
        $consommable->delete();
        return response()->json(['success' => true, 'message' => 'Consommable supprimé']);
    }

    /**
     * Ajuster le stock
     */
    public function ajusterStock(Request $request, Consommable $consommable): JsonResponse
    {
        try {
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
