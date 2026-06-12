<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Equipement;
use App\Models\Categorie;
use App\Models\Agence;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EquipementController extends Controller
{
    /**
     * Lister tous les équipements avec pagination, filtres et recherche
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = Equipement::query()->with([
                'categorie:id,nom',
                'agenceProprietaire:id,nom',
                'agenceActuelle:id,nom',
                'responsable:id,name'
            ]);

            // Scope selon rôle
            if ($user->hasRole('agent')) {
                // Agent ne voit que les équipements affectés à lui
                $query->whereHas('affectations', function ($q) use ($user) {
                    $q->where('agent_id', $user->id)->where('statut', 'active');
                });
            } elseif (!$user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                // Chef d'agence, gestionnaire local, technicien: voit équipements de son agence
                $query->byAgence($user->agence_id);
            }

            if ($request->filled('search')) {
                $query->search($request->search);
            }

            if ($request->filled('agence_id')) {
                $query->byAgence($request->agence_id);
            }

            if ($request->filled('statut_global')) {
                $query->byStatutGlobal($request->statut_global);
            }

            if ($request->filled('etat')) {
                $query->byEtat($request->etat);
            }

            if ($request->filled('categorie_id')) {
                $query->byCategorie($request->categorie_id);
            }

            if ($request->filled('lot_reference')) {
                $query->where('lot_reference', $request->lot_reference);
            }

            if ($request->boolean('garantie_expire_bientot')) {
                $jours = $request->input('jours_garantie', 30);
                $query->garantieExpireSoon($jours);
            }

            if ($request->boolean('disponibles_transfert')) {
                $query->disponiblesTransfert();
            }

            $sortBy = $request->input('sort_by', 'reference');
            $sortOrder = $request->input('sort_order', 'asc');
            
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

            $perPage = min($request->input('per_page', 15), 50);
            $equipements = $query->paginate($perPage);

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
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Vérifier la permission
            if (!$user->can('equipements.create')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas la permission de créer des équipements.'
                ], 403);
            }
            
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'marque' => 'nullable|string|max:255',
                'modele' => 'nullable|string|max:255',
                'categorie_id' => 'required|exists:categories,id',
                'date_acquisition' => 'nullable|date',
                'prix_achat' => 'nullable|numeric|min:0',
                'etat' => 'required|string|in:nouveau,actif,en_maintenance,hors_service,archive',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'quantite_a_creer' => 'nullable|integer|min:1|max:100',
                'mode_enregistrement' => 'nullable|string|in:individuel,lot',
            ]);

            $quantite = $request->input('quantite_a_creer', 1);
            $modeEnregistrement = $request->input('mode_enregistrement', 'individuel');
            $equipements_crees = [];
            $lotReference = ($quantite > 1 && $modeEnregistrement === 'individuel') ? $this->generateUniqueLotReference() : null;

            if ($user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                $agenceProprietaireId = $request->input('agence_proprietaire_id') ?? Agence::where('type', 'generale')->first()->id;
                $agenceActuelleId = $request->input('agence_actuelle_id') ?? $agenceProprietaireId;
            } else {
                $agenceProprietaireId = $user->agence_id;
                $agenceActuelleId = $user->agence_id;
            }

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('equipements/photos', 'public');
            }

            if ($modeEnregistrement === 'lot') {
                // Création d'un seul enregistrement représentant le lot
                $equipementData = [
                    'nom' => $validated['nom'],
                    'quantite' => $quantite,
                    'is_lot' => true,
                    'marque' => $validated['marque'] ?? null,
                    'modele' => $validated['modele'] ?? null,
                    'quantite' => $quantite,
                    'categorie_id' => $validated['categorie_id'],
                    'date_acquisition' => $validated['date_acquisition'] ?? null,
                    'prix_achat' => $validated['prix_achat'] ?? null,
                    'etat' => $validated['etat'],
                    'agence_proprietaire_id' => $agenceProprietaireId,
                    'agence_actuelle_id' => $agenceActuelleId,
                    'statut_global' => $this->mapStatut($validated['etat']),
                    'photo' => $photoPath,
                    'lot_reference' => $this->generateUniqueLotReference(),
                    'reference' => $this->generateUniqueReference(),
                    'code_inventaire' => $this->generateUniqueCodeInventaire(),
                ];

                $equipement = Equipement::create($equipementData);
                
                try {
                    $equipement->generateQRCode();
                } catch (\Exception $e) {
                    \Log::error("Erreur QR Code pour lot {$equipement->id}: " . $e->getMessage());
                }

                $equipement->createMouvement('creation', "Création initiale en lot (Quantité: {$quantite})", $user->id);
                $equipements_crees = $equipement;

            } else {
                // Boucle de création multiple (individuel)
                for ($i = 0; $i < $quantite; $i++) {
                    $equipementData = [
                        'nom' => $quantite > 1 ? ($validated['nom'] . " (" . ($i + 1) . "/" . $quantite . ")") : $validated['nom'],
                        'quantite' => 1,
                        'is_lot' => false,
                        'marque' => $validated['marque'] ?? null,
                        'modele' => $validated['modele'] ?? null,
                        'categorie_id' => $validated['categorie_id'],
                        'date_acquisition' => $validated['date_acquisition'] ?? null,
                        'prix_achat' => $validated['prix_achat'] ?? null,
                        'etat' => $validated['etat'],
                        'agence_proprietaire_id' => $agenceProprietaireId,
                        'agence_actuelle_id' => $agenceActuelleId,
                        'statut_global' => $this->mapStatut($validated['etat']),
                        'photo' => $photoPath,
                        'lot_reference' => $lotReference,
                        'reference' => $this->generateUniqueReference(),
                        'code_inventaire' => $this->generateUniqueCodeInventaire(),
                    ];

                    $equipement = Equipement::create($equipementData);
                    
                    try {
                        $equipement->generateQRCode();
                    } catch (\Exception $e) {
                        \Log::error("Erreur QR Code pour équipement {$equipement->id}: " . $e->getMessage());
                    }

                    $equipement->createMouvement('creation', "Création initiale" . ($quantite > 1 ? " (Partie du lot {$lotReference})" : ""), $user->id);
                    
                    if ($quantite === 1) {
                        $equipements_crees = $equipement;
                    } else {
                        $equipements_crees[] = $equipement;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => ($quantite === 1 || $modeEnregistrement === 'lot') ? $equipements_crees->load(['categorie', 'agenceProprietaire', 'agenceActuelle', 'responsable']) : $equipements_crees,
                'message' => ($quantite > 1 && $modeEnregistrement === 'lot') ? "Lot de {$quantite} équipements créé avec succès" : ($quantite > 1 ? "{$quantite} équipements créés avec succès" : 'Équipement créé avec succès')
            ], 201);



        } catch (\Exception $e) {
            \Log::error('STORE EQUIPEMENT ERROR: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Afficher un équipement
     */
    public function show(Equipement $equipement): JsonResponse
    {
        $user = request()->user();
        
        // Vérifier l'accès
        if ($user->hasRole('agent')) {
            $canView = $equipement->affectations()->where('agent_id', $user->id)->where('statut', 'active')->exists();
            if (!$canView) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas la permission de voir cet équipement.'
                ], 403);
            }
        } elseif (!$user->hasRole(['super_admin', 'gestionnaire_stock_general']) && $equipement->agence_actuelle_id != $user->agence_id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'avez pas la permission de voir cet équipement.'
            ], 403);
        }

        $equipement->load(['categorie', 'agenceProprietaire', 'agenceActuelle', 'responsable', 'consommables', 'mouvements.user']);
        
        $relatedInLot = [];
        if ($equipement->lot_reference) {
            $relatedInLot = Equipement::where('lot_reference', $equipement->lot_reference)
                ->where('id', '!=', $equipement->id)
                ->select('id', 'nom', 'code_inventaire', 'numero_serie', 'etat')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => array_merge($equipement->toArray(), [
                'related_in_lot' => $relatedInLot
            ])
        ]);
    }

    /**
     * Modifier un équipement
     */
    public function update(Request $request, Equipement $equipement): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Vérifier la permission
            if (!$user->can('equipements.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas la permission de modifier des équipements.'
                ], 403);
            }
            
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'quantite' => 'nullable|integer|min:1',
                'quantite_a_creer' => 'nullable|integer|min:1',
                'is_lot' => 'nullable|boolean',
                'mode_enregistrement' => 'nullable|string|in:individuel,lot',
                'numero_serie' => 'nullable|string|max:255|unique:equipements,numero_serie,' . $equipement->id,
                'reference' => 'nullable|string|max:255|unique:equipements,reference,' . $equipement->id,
                'imei' => 'nullable|string|max:255|unique:equipements,imei,' . $equipement->id,
                'code_inventaire' => 'nullable|string|max:255|unique:equipements,code_inventaire,' . $equipement->id,
                'marque' => 'nullable|string|max:255',
                'modele' => 'nullable|string|max:255',
                'categorie_id' => 'required|exists:categories,id',
                'fournisseur' => 'nullable|string|max:255',
                'date_acquisition' => 'nullable|date',
                'prix_achat' => 'nullable|numeric|min:0',
                'garantie_date_fin' => 'nullable|date',
                'etat' => 'required|string|in:nouveau,actif,en_maintenance,hors_service,archive',
                'localisation' => 'nullable|string|max:255',
                'responsable_id' => 'nullable|exists:users,id',
                'quantite' => 'nullable|integer|min:1',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'specifications' => 'nullable|string',
            ]);

            if ($request->hasFile('photo')) {
                if ($equipement->photo) Storage::disk('public')->delete($equipement->photo);
                $validated['photo'] = $request->file('photo')->store('equipements/photos', 'public');
            }

            if (isset($validated['specifications']) && is_string($validated['specifications'])) {
                $validated['specifications'] = json_decode($validated['specifications'], true);
            }

            // Gérer la quantité et le mode lot si envoyés via les noms du formulaire
            if ($request->has('quantite_a_creer')) {
                $validated['quantite'] = $request->quantite_a_creer;
            }
            if ($request->has('mode_enregistrement')) {
                $validated['is_lot'] = $request->mode_enregistrement === 'lot';
            }

            $equipement->update(array_merge($validated, [
                'statut_global' => $this->mapStatut($validated['etat'])
            ]));

            $equipement->createMouvement('modification', "Mise à jour des informations par " . $request->user()->name, $request->user()->id);

            return response()->json([
                'success' => true,
                'data' => $equipement->load(['categorie', 'agenceProprietaire', 'agenceActuelle', 'responsable']),
                'message' => 'Équipement modifié avec succès'
            ]);
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la mise à jour de l'équipement {$equipement->id}: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un équipement (Soft Delete / Mise au rebut)
     */
    public function destroy(Request $request, Equipement $equipement): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Vérifier la permission
            if (!$user->can('equipements.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas la permission de supprimer des équipements.'
                ], 403);
            }
            
            // On change le statut avant le soft delete pour garder la trace métier
            $equipement->update([
                'etat' => 'hors_service',
                'statut_global' => 'reforme'
            ]);

            $equipement->createMouvement('reforme', "Mis au rebut / Supprimé par " . $user->name, $user->id);
            
            $equipement->delete();

            return response()->json([
                'success' => true,
                'message' => 'Équipement mis au rebut et retiré de la liste active.'
            ]);
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la suppression de l'équipement {$equipement->id}: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function mapStatut(string $statut): string
    {
        $map = [
            'nouveau' => 'en_stock_general',
            'actif' => 'en_service',
            'en_maintenance' => 'en_maintenance',
            'hors_service' => 'reforme',
            'archive' => 'archive'
        ];
        return $map[$statut] ?? 'en_service';
    }

    protected function generateUniqueReference(): string
    {
        do {
            $reference = 'REF-' . strtoupper(Str::random(6));
        } while (Equipement::where('reference', $reference)->exists());
        
        return $reference;
    }

    protected function generateUniqueCodeInventaire(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (Equipement::where('code_inventaire', $code)->exists());
        
        return $code;
    }

    protected function generateUniqueLotReference(): string
    {
        do {
            $lot = 'LOT-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        } while (Equipement::where('lot_reference', $lot)->exists());
        
        return $lot;
    }
}
