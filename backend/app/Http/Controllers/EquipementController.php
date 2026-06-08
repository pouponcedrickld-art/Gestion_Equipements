<?php

namespace App\Http\Controllers;

use App\Models\Equipement;
use App\Models\Categorie;
use App\Models\Agence;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EquipementController extends Controller
{
    /**
     * Lister tous les équipements avec pagination, filtres et recherche
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = Equipement::query()->with([
                'categorie:id,nom',
                'agenceProprietaire:id,nom',
                'agenceActuelle:id,nom'
            ]);

            // Scoper par agence selon les permissions
            if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                $query->byAgence($user->agence_id);
            }

            // Recherche multi-critères
            if ($request->filled('search')) {
                $query->search($request->search);
            }

            // Filtrer par agence actuelle
            if ($request->filled('agence_id')) {
                $query->byAgence($request->agence_id);
            }

            // Filtrer par statut global
            if ($request->filled('statut_global')) {
                $query->byStatut($request->statut_global);
            }

            // Filtrer par état
            if ($request->filled('etat')) {
                $query->byEtat($request->etat);
            }

            // Filtrer par catégorie
            if ($request->filled('categorie_id')) {
                $query->byCategorie($request->categorie_id);
            }

            // Filtrer par garantie expirée bientôt
            if ($request->boolean('garantie_expire_bientot')) {
                $jours = $request->input('jours_garantie', 30);
                $query->garantieExpireSoon($jours);
            }

            // Filtrer équipements disponibles pour transfert
            if ($request->boolean('disponibles_transfert')) {
                $query->disponiblesTransfert();
            }

            // Tri
            $sortBy = $request->input('sort_by', 'reference');
            $sortOrder = $request->input('sort_order', 'asc');
            
            // Gérer le tri par relation
            if ($sortBy === 'categorie') {
                $query->join('categories', 'equipements.categorie_id', '=', 'categories.id')
                      ->orderBy('categories.nom', $sortOrder)
                      ->select('equipements.*');
            } elseif ($sortBy === 'agence_actuelle') {
                $query->join('agences', 'equipements.agence_actuelle_id', '=', 'agences.id')
                      ->orderBy('agences.nom', $sortOrder)
                      ->select('equipements.*');
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = min($request->input('per_page', 15), 50); // Max 50 par page
            $equipements = $query->paginate($perPage);

            // Ajouter des informations calculées
            $equipements->getCollection()->transform(function ($equipement) {
                $equipement->statut_garantie = $equipement->statut_garantie;
                $equipement->affectation_en_cours = $equipement->affectation_en_cours;
                $equipement->est_disponible_affectation = $equipement->isDisponiblePourAffectation();
                return $equipement;
            });

            return response()->json([
                'success' => true,
                'data' => $equipements,
                'message' => 'Équipements récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des équipements',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Créer un nouvel équipement
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
                'reference' => 'required|string|max:255|unique:equipements,reference',
                'numero_serie' => 'required|string|max:255|unique:equipements,numero_serie',
                'imei' => 'nullable|string|max:255|unique:equipements,imei',
                'code_inventaire' => 'required|string|max:255|unique:equipements,code_inventaire',
                'marque' => 'nullable|string|max:255',
                'modele' => 'nullable|string|max:255',
                'categorie_id' => 'required|exists:categories,id',
                'fournisseur' => 'nullable|string|max:255',
                'date_acquisition' => 'nullable|date',
                'prix_achat' => 'nullable|numeric|min:0',
                'garantie_date_fin' => 'nullable|date|after:date_acquisition',
                'etat' => 'required|string|in:neuf,en_service,en_panne,en_maintenance,reforme,perdu',
                'localisation' => 'nullable|string|max:255',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // 2MB max
            ], [
                'reference.required' => 'La référence est obligatoire',
                'reference.unique' => 'Cette référence existe déjà',
                'numero_serie.required' => 'Le numéro de série est obligatoire',
                'numero_serie.unique' => 'Ce numéro de série existe déjà',
                'imei.unique' => 'Cet IMEI existe déjà',
                'code_inventaire.required' => 'Le code inventaire est obligatoire',
                'code_inventaire.unique' => 'Ce code inventaire existe déjà',
                'categorie_id.required' => 'La catégorie est obligatoire',
                'categorie_id.exists' => 'La catégorie sélectionnée n\'existe pas',
                'etat.required' => 'L\'état est obligatoire',
                'etat.in' => 'État invalide',
                'prix_achat.numeric' => 'Le prix d\'achat doit être un nombre',
                'prix_achat.min' => 'Le prix d\'achat ne peut pas être négatif',
                'garantie_date_fin.after' => 'La date de fin de garantie doit être postérieure à la date d\'acquisition',
                'photo.image' => 'Le fichier doit être une image',
                'photo.mimes' => 'L\'image doit être au format JPEG, PNG ou JPG',
                'photo.max' => 'L\'image ne peut pas dépasser 2MB',
            ]);

            // Déterminer l'agence selon le rôle utilisateur
            if ($user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                // Peut être assigné au stock général
                $agenceProprietaire = Agence::where('type', 'generale')->first();
                $agenceActuelle = $agenceProprietaire;
                $statutGlobal = 'en_stock_general';
            } else {
                // Assigné à l'agence de l'utilisateur
                $agenceProprietaire = $user->agence;
                $agenceActuelle = $user->agence;
                $statutGlobal = $user->agence->type === 'generale' ? 'en_stock_general' : 'en_stock_local';
            }

            // Gestion de l'upload de photo
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $filename = 'equipement_' . Str::random(10) . '_' . time() . '.' . $photo->getClientOriginalExtension();
                $photoPath = $photo->storeAs('equipements/photos', $filename, 'public');
            }

            // Préparer les données pour la création
            $equipementData = array_merge($validated, [
                'agence_proprietaire_id' => $agenceProprietaire->id,
                'agence_actuelle_id' => $agenceActuelle->id,
                'statut_global' => $statutGlobal,
                'photo' => $photoPath,
            ]);

            // Retirer la photo des données validées
            unset($equipementData['photo_file']);

            // Créer l'équipement
            $equipement = Equipement::create($equipementData);

            // Générer le QR code
            $qrCode = $equipement->generateQRCode();

            // Créer le mouvement initial
            $equipement->createMouvement(
                'creation',
                "Création initiale - {$equipement->marque} {$equipement->modele}",
                $user->id
            );

            // Charger les relations pour la réponse
            $equipement->load(['categorie', 'agenceProprietaire', 'agenceActuelle']);

            return response()->json([
                'success' => true,
                'data' => $equipement,
                'message' => 'Équipement créé avec succès'
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
                'message' => 'Erreur lors de la création de l\'équipement',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Afficher un équipement avec toutes ses informations
     * 
     * @param Equipement $equipement
     * @return JsonResponse
     */
    public function show(Equipement $equipement): JsonResponse
    {
        try {
            // Charger toutes les relations importantes
            $equipement->load([
                'categorie',
                'agenceProprietaire',
                'agenceActuelle',
                'consommables',
                'affectations' => function ($query) {
                    $query->with('agent')->latest()->limit(5);
                },
                'mouvements' => function ($query) {
                    $query->with('user:id,name')->latest()->limit(10);
                },
                'pannes' => function ($query) {
                    $query->latest()->limit(3);
                },
                'transferts' => function ($query) {
                    $query->with(['agenceSource', 'agenceDestination'])->latest()->limit(3);
                }
            ]);

            // Ajouter des informations calculées
            $equipement->statut_garantie = $equipement->statut_garantie;
            $equipement->affectation_en_cours = $equipement->affectation_en_cours;
            $equipement->est_disponible_affectation = $equipement->isDisponiblePourAffectation();

            // Statistiques supplémentaires
            $stats = [
                'nombre_affectations' => $equipement->affectations()->count(),
                'nombre_pannes' => $equipement->pannes()->count(),
                'nombre_transferts' => $equipement->transferts()->count(),
                'nombre_mouvements' => $equipement->mouvements()->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'equipement' => $equipement,
                    'statistiques' => $stats
                ],
                'message' => 'Équipement récupéré avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'équipement',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Modifier un équipement
     * 
     * @param Request $request
     * @param Equipement $equipement
     * @return JsonResponse
     */
    public function update(Request $request, Equipement $equipement): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Validation des données
            $validated = $request->validate([
                'reference' => 'required|string|max:255|unique:equipements,reference,' . $equipement->id,
                'numero_serie' => 'required|string|max:255|unique:equipements,numero_serie,' . $equipement->id,
                'imei' => 'nullable|string|max:255|unique:equipements,imei,' . $equipement->id,
                'code_inventaire' => 'required|string|max:255|unique:equipements,code_inventaire,' . $equipement->id,
                'marque' => 'nullable|string|max:255',
                'modele' => 'nullable|string|max:255',
                'categorie_id' => 'required|exists:categories,id',
                'fournisseur' => 'nullable|string|max:255',
                'date_acquisition' => 'nullable|date',
                'prix_achat' => 'nullable|numeric|min:0',
                'garantie_date_fin' => 'nullable|date|after:date_acquisition',
                'etat' => 'required|string|in:neuf,en_service,en_panne,en_maintenance,reforme,perdu',
                'localisation' => 'nullable|string|max:255',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ], [
                // Messages d'erreur similaires au store()
                'reference.required' => 'La référence est obligatoire',
                'reference.unique' => 'Cette référence existe déjà',
                // ... (mêmes messages que store)
            ]);

            // Gestion de l'upload de nouvelle photo
            if ($request->hasFile('photo')) {
                // Supprimer l'ancienne photo
                if ($equipement->photo && Storage::disk('public')->exists($equipement->photo)) {
                    Storage::disk('public')->delete($equipement->photo);
                }

                $photo = $request->file('photo');
                $filename = 'equipement_' . Str::random(10) . '_' . time() . '.' . $photo->getClientOriginalExtension();
                $validated['photo'] = $photo->storeAs('equipements/photos', $filename, 'public');
            }

            // Détecter les changements importants pour créer des mouvements
            $changementsImportants = [];
            
            if ($equipement->etat !== $validated['etat']) {
                $changementsImportants[] = "État: {$equipement->etat} → {$validated['etat']}";
            }
            
            if ($equipement->localisation !== $validated['localisation']) {
                $changementsImportants[] = "Localisation: {$equipement->localisation} → {$validated['localisation']}";
            }

            // Mettre à jour l'équipement
            $equipement->update($validated);

            // Créer un mouvement si des changements importants
            if (!empty($changementsImportants)) {
                $description = "Modification: " . implode(', ', $changementsImportants);
                $equipement->createMouvement(
                    'modification',
                    $description,
                    $user->id
                );
            }

            // Recharger avec les relations
            $equipement->load(['categorie', 'agenceProprietaire', 'agenceActuelle']);

            return response()->json([
                'success' => true,
                'data' => $equipement,
                'message' => 'Équipement modifié avec succès'
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
                'message' => 'Erreur lors de la modification de l\'équipement',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Supprimer un équipement (soft delete)
     * 
     * @param Equipement $equipement
     * @return JsonResponse
     */
    public function destroy(Equipement $equipement): JsonResponse
    {
        try {
            // Vérifier si l'équipement peut être supprimé
            if ($equipement->affectations()->where('statut', 'active')->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet équipement ne peut pas être supprimé car il est actuellement affecté',
                ], 400);
            }

            if ($equipement->transferts()->whereIn('statut', ['demande', 'approuve', 'expedie'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet équipement ne peut pas être supprimé car il est impliqué dans des transferts en cours',
                ], 400);
            }

            // Créer un mouvement de suppression
            $equipement->createMouvement(
                'suppression',
                "Suppression de l'équipement {$equipement->reference}",
                request()->user()->id
            );

            $reference = $equipement->reference;
            $equipement->delete(); // Soft delete

            return response()->json([
                'success' => true,
                'message' => "Équipement '{$reference}' supprimé avec succès"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'équipement',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Générer un QR code pour un équipement
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function generateQr(Request $request, $id): JsonResponse
    {
        try {
            $equipement = Equipement::findOrFail($id);
            
            // Générer le QR code
            $qrCode = $equipement->generateQRCode();

            // Créer un mouvement
            $equipement->createMouvement(
                'qr_generation',
                "Génération QR code: {$qrCode}",
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'qr_code' => $qrCode,
                    'equipement_id' => $equipement->id,
                    'reference' => $equipement->reference
                ],
                'message' => 'QR code généré avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du QR code',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Recherche avancée d'équipements
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Validation des paramètres de recherche
            $validated = $request->validate([
                'term' => 'nullable|string|max:255',
                'filters' => 'nullable|array',
                'filters.categories' => 'nullable|array',
                'filters.agences' => 'nullable|array', 
                'filters.statuts' => 'nullable|array',
                'filters.etats' => 'nullable|array',
                'filters.date_acquisition_from' => 'nullable|date',
                'filters.date_acquisition_to' => 'nullable|date',
                'limit' => 'nullable|integer|min:1|max:100'
            ]);

            $query = Equipement::query()->with(['categorie:id,nom', 'agenceActuelle:id,nom']);

            // Scoper par agence selon les permissions
            if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                $query->byAgence($user->agence_id);
            }

            // Recherche par terme
            if (!empty($validated['term'])) {
                $query->search($validated['term']);
            }

            // Appliquer les filtres
            if (!empty($validated['filters'])) {
                $filters = $validated['filters'];

                if (!empty($filters['categories'])) {
                    $query->whereIn('categorie_id', $filters['categories']);
                }

                if (!empty($filters['agences'])) {
                    $query->whereIn('agence_actuelle_id', $filters['agences']);
                }

                if (!empty($filters['statuts'])) {
                    $query->whereIn('statut_global', $filters['statuts']);
                }

                if (!empty($filters['etats'])) {
                    $query->whereIn('etat', $filters['etats']);
                }

                if (!empty($filters['date_acquisition_from'])) {
                    $query->whereDate('date_acquisition', '>=', $filters['date_acquisition_from']);
                }

                if (!empty($filters['date_acquisition_to'])) {
                    $query->whereDate('date_acquisition', '<=', $filters['date_acquisition_to']);
                }
            }

            // Limiter les résultats
            $limit = $validated['limit'] ?? 20;
            $equipements = $query->orderBy('reference')->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => $equipements,
                'total' => $equipements->count(),
                'message' => 'Recherche effectuée avec succès'
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
                'message' => 'Erreur lors de la recherche',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Import d'équipements en masse 
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB max
                'agence_id' => 'nullable|exists:agences,id',
                'preview_only' => 'nullable|boolean'
            ], [
                'file.required' => 'Le fichier est obligatoire',
                'file.mimes' => 'Le fichier doit être au format CSV ou Excel',
                'file.max' => 'Le fichier ne peut pas dépasser 10MB',
                'agence_id.exists' => 'L\'agence sélectionnée n\'existe pas',
            ]);

            $importService = app(\App\Services\ImportService::class);
            
            // Si c'est juste une prévisualisation
            if ($request->boolean('preview_only')) {
                $maxLignes = $request->input('max_preview', 10);
                $preview = $importService->previewImport($request->file('file'), $maxLignes);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Prévisualisation générée avec succès',
                    'data' => $preview
                ]);
            }

            // Import réel
            $agenceId = $request->input('agence_id');
            
            // Si pas d'agence spécifiée, utiliser celle de l'utilisateur ou l'agence générale
            if (!$agenceId) {
                if ($user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                    $agenceId = null; // Sera déterminé dans le service (agence générale)
                } else {
                    $agenceId = $user->agence_id;
                }
            }

            $resultat = $importService->importEquipements(
                $request->file('file'), 
                $user->id, 
                $agenceId
            );

            return response()->json($resultat);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'import',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Télécharger le template d'import CSV
     * 
     * @return \Illuminate\Http\Response
     */
    public function downloadTemplate(): \Illuminate\Http\Response
    {
        try {
            $importService = app(\App\Services\ImportService::class);
            $csvContent = $importService->genererTemplateCSV();

            return response($csvContent, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="template_import_equipements.csv"',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du template',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
