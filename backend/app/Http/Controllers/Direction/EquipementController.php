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

            if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                $query->byAgence($user->agence_id);
            }

            if ($request->filled('search')) {
                $query->search($request->search);
            }

            if ($request->filled('agence_id')) {
                $query->byAgence($request->agence_id);
            }

            if ($request->filled('statut_global')) {
                $query->byStatut($request->statut_global);
            }

            if ($request->filled('etat')) {
                $query->byEtat($request->etat);
            }

            if ($request->filled('categorie_id')) {
                $query->byCategorie($request->categorie_id);
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
            
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'reference' => 'nullable|string|max:255|unique:equipements,reference',
                'numero_serie' => 'required|string|max:255|unique:equipements,numero_serie',
                'imei' => 'nullable|string|max:255|unique:equipements,imei',
                'code_inventaire' => 'nullable|string|max:255|unique:equipements,code_inventaire',
                'marque' => 'nullable|string|max:255',
                'modele' => 'nullable|string|max:255',
                'categorie_id' => 'required|exists:categories,id',
                'fournisseur' => 'nullable|string|max:255',
                'date_acquisition' => 'nullable|date',
                'prix_achat' => 'nullable|numeric|min:0',
                'garantie_date_fin' => 'nullable|date|after_or_equal:date_acquisition',
                'etat' => 'required|string|in:nouveau,actif,en_maintenance,hors_service,archive',
                'localisation' => 'nullable|string|max:255',
                'responsable_id' => 'nullable|exists:users,id',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'specifications' => 'nullable|string', // Reçu sous forme de JSON string
            ]);

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

            $equipementData = array_merge($validated, [
                'agence_proprietaire_id' => $agenceProprietaireId,
                'agence_actuelle_id' => $agenceActuelleId,
                'statut_global' => $this->mapStatut($validated['etat']),
                'photo' => $photoPath,
                'reference' => $validated['reference'] ?? ('REF-' . strtoupper(Str::random(6))),
                'code_inventaire' => $validated['code_inventaire'] ?? strtoupper(Str::random(10)),
                'specifications' => isset($validated['specifications']) ? json_decode($validated['specifications'], true) : null,
            ]);

            $equipement = Equipement::create($equipementData);
            $equipement->generateQRCode();
            $equipement->createMouvement('creation', "Création initiale", $user->id);

            return response()->json([
                'success' => true,
                'data' => $equipement->load(['categorie', 'agenceProprietaire', 'agenceActuelle', 'responsable']),
                'message' => 'Équipement créé avec succès'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un équipement
     */
    public function show(Equipement $equipement): JsonResponse
    {
        $equipement->load(['categorie', 'agenceProprietaire', 'agenceActuelle', 'responsable', 'consommables', 'mouvements.user']);
        return response()->json([
            'success' => true,
            'data' => $equipement
        ]);
    }

    /**
     * Modifier un équipement
     */
    public function update(Request $request, Equipement $equipement): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'numero_serie' => 'required|string|max:255|unique:equipements,numero_serie,' . $equipement->id,
                'categorie_id' => 'required|exists:categories,id',
                'etat' => 'required|string|in:nouveau,actif,en_maintenance,hors_service,archive',
                'localisation' => 'nullable|string|max:255',
                'responsable_id' => 'nullable|exists:users,id',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'specifications' => 'nullable|string',
            ]);

            if ($request->hasFile('photo')) {
                if ($equipement->photo) Storage::disk('public')->delete($equipement->photo);
                $validated['photo'] = $request->file('photo')->store('equipements/photos', 'public');
            }

            if (isset($validated['specifications'])) {
                $validated['specifications'] = json_decode($validated['specifications'], true);
            }

            $equipement->update(array_merge($validated, [
                'statut_global' => $this->mapStatut($validated['etat'])
            ]));

            return response()->json([
                'success' => true,
                'data' => $equipement->load(['categorie', 'agenceProprietaire', 'agenceActuelle', 'responsable']),
                'message' => 'Équipement modifié avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Supprimer un équipement (Soft Delete / Mise au rebut)
     */
    public function destroy(Request $request, Equipement $equipement): JsonResponse
    {
        try {
            $user = $request->user();
            
            // On change le statut avant le soft delete pour garder la trace métier
            $equipement->update([
                'etat' => 'hors_service',
                'statut_global' => 'reforme'
            ]);

            $equipement->createMouvement('suppression', "Mis au rebut / Supprimé par " . $user->name, $user->id);
            
            $equipement->delete();

            return response()->json([
                'success' => true,
                'message' => 'Équipement mis au rebut et retiré de la liste active.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
}
