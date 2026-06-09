<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CategorieController extends Controller
{
    /**
     * Lister toutes les catégories avec pagination et recherche
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Categorie::query()->with(['parent', 'equipements:id,categorie_id,statut_global']);

            if ($request->filled('search')) {
                $query->search($request->search);
            }

            if ($request->boolean('with_equipements_only')) {
                $query->withEquipements();
            }

            $sortBy = $request->input('sort_by', 'nom');
            $sortOrder = $request->input('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = min($request->input('per_page', 15), 50);
            $categories = $query->paginate($perPage);

            $categories->getCollection()->transform(function ($categorie) {
                $categorie->nombre_equipements = $categorie->equipements->count();
                unset($categorie->equipements);
                return $categorie;
            });

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Catégories récupérées avec succès'
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
     * Créer une nouvelle catégorie
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:255|unique:categories,nom',
                'code' => 'nullable|string|max:50|unique:categories,code',
                'description' => 'nullable|string|max:1000',
                'parent_id' => 'nullable|exists:categories,id',
                'frequence_maintenance' => 'nullable|integer|min:1',
                'duree_vie' => 'nullable|integer|min:1',
                'attributs_personnalises' => 'nullable|array',
            ]);

            $categorie = Categorie::create($validated);

            return response()->json([
                'success' => true,
                'data' => $categorie->load('parent'),
                'message' => 'Catégorie créée avec succès'
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Afficher une catégorie
     */
    public function show(Categorie $categorie): JsonResponse
    {
        $categorie->load(['parent', 'enfants', 'equipements']);
        return response()->json([
            'success' => true,
            'data' => $categorie
        ]);
    }

    /**
     * Modifier une catégorie
     */
    public function update(Request $request, Categorie $categorie): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:255|unique:categories,nom,' . $categorie->id,
                'code' => 'nullable|string|max:50|unique:categories,code,' . $categorie->id,
                'description' => 'nullable|string|max:1000',
                'parent_id' => 'nullable|exists:categories,id',
                'frequence_maintenance' => 'nullable|integer|min:1',
                'duree_vie' => 'nullable|integer|min:1',
                'attributs_personnalises' => 'nullable|array',
            ]);

            $categorie->update($validated);

            return response()->json([
                'success' => true,
                'data' => $categorie->fresh()->load('parent'),
                'message' => 'Catégorie modifiée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Supprimer une catégorie
     */
    public function destroy(Categorie $categorie): JsonResponse
    {
        if (!$categorie->canBeDeleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette catégorie contient des équipements et ne peut être supprimée.'
            ], 400);
        }

        $categorie->delete();
        return response()->json(['success' => true, 'message' => 'Catégorie supprimée']);
    }

    /**
     * Liste simple pour sélecteurs
     */
    public function list(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Categorie::select('id', 'nom', 'code')->orderBy('nom')->get()
        ]);
    }
}
