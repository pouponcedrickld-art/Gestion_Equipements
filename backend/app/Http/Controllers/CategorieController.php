<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CategorieController extends Controller
{
    /**
     * Lister toutes les catégories avec pagination et recherche
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Categorie::query()->with(['equipements:id,categorie_id,statut_global']);

            // Recherche par terme
            if ($request->filled('search')) {
                $query->search($request->search);
            }

            // Filtrer seulement celles avec équipements si demandé
            if ($request->boolean('with_equipements_only')) {
                $query->withEquipements();
            }

            // Tri
            $sortBy = $request->input('sort_by', 'nom');
            $sortOrder = $request->input('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = min($request->input('per_page', 15), 50); // Max 50 par page
            $categories = $query->paginate($perPage);

            // Ajouter les statistiques pour chaque catégorie
            $categories->getCollection()->transform(function ($categorie) {
                $categorie->nombre_equipements = $categorie->equipements->count();
                $categorie->equipements_par_statut = $categorie->equipements->groupBy('statut_global')
                    ->map(fn($items) => $items->count());
                
                // Nettoyer pour éviter de renvoyer tous les équipements
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
                'message' => 'Erreur lors de la récupération des catégories',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Créer une nouvelle catégorie
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validation des données
            $validated = $request->validate([
                'nom' => 'required|string|max:255|unique:categories,nom',
                'description' => 'nullable|string|max:1000',
            ], [
                'nom.required' => 'Le nom de la catégorie est obligatoire',
                'nom.unique' => 'Cette catégorie existe déjà',
                'nom.max' => 'Le nom ne peut pas dépasser 255 caractères',
                'description.max' => 'La description ne peut pas dépasser 1000 caractères',
            ]);

            // Créer la catégorie
            $categorie = Categorie::create($validated);

            return response()->json([
                'success' => true,
                'data' => $categorie,
                'message' => 'Catégorie créée avec succès'
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
                'message' => 'Erreur lors de la création de la catégorie',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Afficher une catégorie avec ses équipements
     * 
     * @param Categorie $categorie
     * @return JsonResponse
     */
    public function show(Categorie $categorie): JsonResponse
    {
        try {
            // Charger les équipements avec leurs relations essentielles
            $categorie->load([
                'equipements' => function ($query) {
                    $query->select('id', 'reference', 'marque', 'modele', 'statut_global', 'etat', 'agence_actuelle_id', 'categorie_id')
                          ->with('agenceActuelle:id,nom');
                }
            ]);

            // Statistiques de la catégorie
            $stats = [
                'total_equipements' => $categorie->equipements->count(),
                'par_statut' => $categorie->equipements->groupBy('statut_global')->map(fn($items) => $items->count()),
                'par_etat' => $categorie->equipements->groupBy('etat')->map(fn($items) => $items->count()),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'categorie' => $categorie,
                    'statistiques' => $stats
                ],
                'message' => 'Catégorie récupérée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la catégorie',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Modifier une catégorie
     * 
     * @param Request $request
     * @param Categorie $categorie
     * @return JsonResponse
     */
    public function update(Request $request, Categorie $categorie): JsonResponse
    {
        try {
            // Validation des données
            $validated = $request->validate([
                'nom' => 'required|string|max:255|unique:categories,nom,' . $categorie->id,
                'description' => 'nullable|string|max:1000',
            ], [
                'nom.required' => 'Le nom de la catégorie est obligatoire',
                'nom.unique' => 'Cette catégorie existe déjà',
                'nom.max' => 'Le nom ne peut pas dépasser 255 caractères',
                'description.max' => 'La description ne peut pas dépasser 1000 caractères',
            ]);

            // Mettre à jour la catégorie
            $categorie->update($validated);

            return response()->json([
                'success' => true,
                'data' => $categorie->fresh(), // Recharger depuis la DB
                'message' => 'Catégorie modifiée avec succès'
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
                'message' => 'Erreur lors de la modification de la catégorie',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Supprimer une catégorie
     * 
     * @param Categorie $categorie
     * @return JsonResponse
     */
    public function destroy(Categorie $categorie): JsonResponse
    {
        try {
            // Vérifier si la catégorie peut être supprimée
            if (!$categorie->canBeDeleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette catégorie ne peut pas être supprimée car elle contient des équipements',
                    'data' => [
                        'nombre_equipements' => $categorie->equipements()->count()
                    ]
                ], 400);
            }

            // Supprimer la catégorie
            $nom = $categorie->nom;
            $categorie->delete();

            return response()->json([
                'success' => true,
                'message' => "Catégorie '{$nom}' supprimée avec succès"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la catégorie',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtenir toutes les catégories pour les listes déroulantes (simple)
     * 
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        try {
            $categories = Categorie::select('id', 'nom')
                ->orderBy('nom')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Liste des catégories récupérée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la liste des catégories',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
