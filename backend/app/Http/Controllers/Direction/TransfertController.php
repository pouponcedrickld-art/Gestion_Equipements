<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Transfert;
use App\Models\Equipement;
use App\Models\Agence;
use App\Models\DemandeMateriel;
use App\Events\TransfertCree;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransfertController extends Controller
{
    /**
     * Lister tous les transferts avec pagination et filtres
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('kanban')) {
            return $this->indexKanban();
        }

        try {
            $user = $request->user();
            $query = Transfert::query()->with([
                'equipement:id,reference,marque,modele,nom',
                'agenceSource:id,nom,ville',
                'agenceDestination:id,nom,ville',
                'demandePar:id,name',
                'validePar:id,name'
            ]);

            if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                $query->forAgence($user->agence_id);
            }

            if ($request->filled('statut')) {
                $query->byStatut($request->statut);
            }

            if ($request->filled('type_transfert')) {
                $query->byType($request->type_transfert);
            }

            if ($request->filled('direction')) {
                $direction = $request->direction;
                if ($direction === 'entrants') {
                    $query->entrants($user->agence_id);
                } elseif ($direction === 'sortants') {
                    $query->sortants($user->agence_id);
                }
            }

            $sortBy = $request->input('sort_by', 'date_demande');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = min($request->input('per_page', 15), 50);
            $transferts = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $transferts,
                'message' => 'Transferts récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Liste des transferts pour le kanban
     */
    protected function indexKanban()
    {
        // 1. À expédier (Demandes approuvées)
        $a_expedier = Transfert::with(['equipement', 'agenceDestination'])
            ->whereIn('statut', ['approuve', 'demande'])
            ->get()
            ->map(fn($t) => $this->mapForKanban($t, 'transfert'));

        // 2. En transit
        $en_transit = Transfert::with(['equipement', 'agenceDestination'])
            ->where('statut', 'expedie')
            ->get()
            ->map(fn($t) => $this->mapForKanban($t, 'transfert'));

        // 3. Reçu
        $recu = Transfert::with(['equipement', 'agenceDestination'])
            ->where('statut', 'recu')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($t) => $this->mapForKanban($t, 'transfert'));

        return response()->json([
            'a_expedier' => $a_expedier,
            'en_transit' => $en_transit,
            'recu' => $recu
        ]);
    }

    protected function mapForKanban($item, $type)
    {
        $equipement = $item->equipement;
        $nomMateriel = $equipement?->nom;
        if (!$nomMateriel && $equipement) {
            $nomMateriel = ($equipement->marque ?? '') . ' ' . ($equipement->modele ?? '');
        }
        $nomMateriel = $nomMateriel ?: 'Équipement #' . $item->equipement_id;

        return [
            'id' => $type . '_' . $item->id,
            'real_id' => $item->id,
            'type' => $type,
            'nom_materiel' => $nomMateriel,
            'agence' => $item->agenceDestination?->nom ?? 'N/A',
            'date' => $item->date_expedition ?? $item->date_demande,
            'statut' => $item->statut
        ];
    }

    /**
     * Créer un transfert (Brouillon)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validate([
                'equipement_id' => 'required|exists:equipements,id',
                'agence_destination_id' => 'required|exists:agences,id',
                'type_transfert' => 'required|string|in:livraison_generale,retour_generale,transfert_interne',
                'observations' => 'nullable|string|max:1000',
            ]);

            $equipement = Equipement::findOrFail($validated['equipement_id']);
            
            $transfert = Transfert::create(array_merge($validated, [
                'agence_source_id' => $equipement->agence_actuelle_id,
                'demande_par_id' => $user->id,
                'date_demande' => now(),
                'statut' => 'demande',
                'quantite' => 1,
            ]));

            event(new TransfertCree());

            return response()->json([
                'success' => true,
                'data' => $transfert,
                'message' => 'Transfert créé avec succès'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(Transfert $transfert): JsonResponse
    {
        $transfert->load(['equipement', 'agenceSource', 'agenceDestination', 'demandePar', 'validePar']);
        return response()->json(['success' => true, 'data' => $transfert]);
    }

    public function approuver(Request $request, $id): JsonResponse
    {
        try {
            $transfert = Transfert::findOrFail($id);
            $transfert->approuver($request->user()->id);
            return response()->json(['success' => true, 'message' => 'Transfert validé, en attente d\'expédition']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function refuser(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $transfert = Transfert::findOrFail($id);
            
            // Sécurité : Une agence ne peut refuser que ses propres transferts (source ou destination)
            if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                if ($user->agence_id !== $transfert->agence_source_id && $user->agence_id !== $transfert->agence_destination_id) {
                    return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
                }
            }

            $transfert->refuser($user->id, $request->observations);
            return response()->json(['success' => true, 'message' => 'Transfert refusé / annulé']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function expedier(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $transfert = Transfert::findOrFail($id);

            // Sécurité : Seul l'expéditeur (source) ou GSG peut expédier
            if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                if ($user->agence_id !== $transfert->agence_source_id) {
                    return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
                }
            }

            $transfert->expedier($user->id);
            return response()->json(['success' => true, 'message' => 'Équipement expédié (En transit)']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function recevoir(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $transfert = Transfert::findOrFail($id);

            // Sécurité : Seul le destinataire ou GSG peut recevoir
            if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
                if ($user->agence_id !== $transfert->agence_destination_id) {
                    return response()->json(['success' => false, 'message' => 'Seul le destinataire peut confirmer la réception'], 403);
                }
            }

            $transfert->recevoir($user->id);
            return response()->json(['success' => true, 'message' => 'Transfert reçu, stock agence mis à jour']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Liste des demandes approuvées à transférer
     */
    public function getApprovedDemandes(): JsonResponse
    {
        try {
            $demandes = DemandeMateriel::with(['agence', 'equipement', 'chefAgence'])
                
                ->where('statut', 'approuvé')
                ->whereDoesntHave('transferts', function($query) {
                    $query->whereIn('statut', ['demande', 'approuve', 'expedie', 'recu']);
                })
                ->get();

            return response()->json([
                'success' => true,
                'data' => $demandes
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Créer un transfert à partir d'une demande approuvée
     */
    public function createFromDemande(Request $request, $demandeId): JsonResponse
    {
        try {
            $user = $request->user();
            $demande = DemandeMateriel::findOrFail($demandeId);

            if ($demande->statut !== 'approuvé') {
                throw new \Exception("Seules les demandes approuvées peuvent être transférées.");
            }

            $existingTransfert = Transfert::where('demande_materiel_id', $demande->id)
                ->whereIn('statut', ['demande', 'approuve', 'expedie', 'recu'])
                ->first();

            if ($existingTransfert) {
                throw new \Exception("Un transfert existe déjà pour cette demande.");
            }

            $equipement = $demande->equipement;

            DB::transaction(function () use ($demande, $equipement, $user) {
                // Get Siège Social (Agence Générale) if agence_actuelle_id is null
                $agenceGenerale = Agence::where('type', 'generale')->first();
                $agenceSourceId = $equipement->agence_actuelle_id ?? $agenceGenerale?->id;
                
                Transfert::create([
                    'demande_materiel_id' => $demande->id,
                    'equipement_id' => $equipement->id,
                    'agence_source_id' => $agenceSourceId,
                    'agence_destination_id' => $demande->agence_id,
                    'type_transfert' => 'livraison_generale',
                    'statut' => 'approuve',
                    'demande_par_id' => $demande->chef_agence_id,
                    'valide_par_id' => $user->id,
                    'date_demande' => $demande->created_at,
                    'quantite' => $demande->quantite,
                    'observations' => "Transfert généré depuis la demande #" . $demande->id . ". Motif: " . $demande->motif,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Transfert généré avec succès. Vous pouvez maintenant l\'expédier.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Traiter la réception d'un transfert expédié (agence destination).
     *
     * Cette méthode centralise la décision de l'agence destinataire :
     *   - 'accepte' → confirme la réception, incrémente le stock local de l'agence destination.
     *   - 'refuse'  → enregistre le motif de refus et marque l'équipement à retourner
     *                 (il apparaîtra dans le menu "Retours" de l'agence destination).
     *
     * @route PATCH /api/transferts/{id}/traiter-reception
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  Identifiant du transfert
     * @return \Illuminate\Http\JsonResponse
     */
    public function traiterReception(Request $request, $id): JsonResponse
    {
        try {
            $user     = $request->user();
            $transfert = Transfert::with(['equipement', 'agenceSource', 'agenceDestination'])
                                   ->findOrFail($id);

            // ── Vérification des permissions ────────────────────────────────────────
            // Seul l'utilisateur appartenant à l'agence de destination (ou un admin) peut décider.
            if (
                !$user->hasRole(['super_admin', 'gestionnaire_stock_general']) &&
                $user->agence_id !== $transfert->agence_destination_id
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seule l\'agence destinataire peut traiter cette réception.'
                ], 403);
            }

            // ── Vérification du statut courant ───────────────────────────────────────
            // On peut traiter les transferts approuvés ('approuve') ou expédiés ('expedie').
            // Un transfert 'approuve' peut être accepté/refusé directement par l'agence
            // sans nécessiter l'étape formelle d'expédition par le GSG.
            if (!in_array($transfert->statut, ['approuve', 'expedie'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Ce transfert ne peut pas être traité : statut actuel '{$transfert->statut}' (attendu : 'approuve' ou 'expedie')."
                ], 400);
            }

            // ── Validation des données envoyées par le frontend ──────────────────────
            $validated = $request->validate([
                // Le choix de l'agence : 'accepte' ou 'refuse'
                'statut'      => 'required|string|in:accepte,refuse',
                // Obligatoire uniquement en cas de refus
                'motif_refus' => 'required_if:statut,refuse|nullable|string|max:1000',
            ], [
                'statut.required'            => 'La décision (accepte/refuse) est obligatoire.',
                'statut.in'                  => 'La décision doit être "accepte" ou "refuse".',
                'motif_refus.required_if'    => 'Le motif de refus est obligatoire pour un refus.',
                'motif_refus.max'            => 'Le motif de refus ne peut pas dépasser 1000 caractères.',
            ]);

            // ── Traitement dans une transaction pour garantir la cohérence des données ─
            DB::beginTransaction();

            try {
                if ($validated['statut'] === 'accepte') {
                    // ── CAS 1 : ACCEPTATION ─────────────────────────────────────────
                    // La méthode recevoir() du modèle Transfert effectue automatiquement :
                    //   • Mise à jour du statut du transfert → 'recu'
                    //   • Mise à jour de l'agence_actuelle_id de l'équipement
                    //   • Incrémentation du stock de l'agence destination via StockAgenceService
                    //   • Création d'un mouvement de traçabilité
                    $transfert->recevoir($user->id);

                    $message = "Équipement '{$transfert->equipement->nom}' accepté et ajouté au stock de l'agence.";

                } else {
                    // ── CAS 2 : REFUS ───────────────────────────────────────────────
                    // La méthode refuser() du modèle Transfert :
                    //   • Passe le statut du transfert → 'refuse'
                    //   • Enregistre le motif_refus
                    $transfert->refuser($user->id, $validated['motif_refus']);

                    // L'équipement doit retourner à la source.
                    // On le marque 'en_retour' pour qu'il apparaisse dans le menu "Retours"
                    // de l'agence destination (où il se trouve physiquement).
                    if ($transfert->equipement) {
                        $transfert->equipement->update([
                            'statut_global' => 'en_retour',
                        ]);

                        // Traçabilité : création d'un mouvement pour journaliser le refus
                        $transfert->equipement->createMouvement(
                            'retour',
                            "Équipement refusé à la réception par {$user->name}. Motif : {$validated['motif_refus']}",
                            $user->id,
                            ['agence_id' => $transfert->agence_destination_id], // valeur avant
                            ['agence_id' => $transfert->agence_source_id]        // valeur cible (retour)
                        );

                        // Création automatique d'un transfert de retour pour que l'équipement
                        // s'affiche dans le menu "Retours" de l'agence (transferts sortants).
                        \App\Models\Transfert::create([
                            'equipement_id' => $transfert->equipement_id,
                            'agence_source_id' => $transfert->agence_destination_id,
                            'agence_destination_id' => $transfert->agence_source_id,
                            'demande_par_id' => $user->id,
                            'type_transfert' => 'retour_generale',
                            'statut' => 'demande',
                            'date_demande' => now(),
                            'observations' => "Retour automatique suite au refus de réception. Motif : {$validated['motif_refus']}",
                        ]);
                    }

                    $message = "Transfert refusé. L'équipement est en attente de retour à l'agence source.";
                }

                DB::commit();

                // Rechargement des relations pour la réponse complète
                $transfert->load(['equipement', 'agenceSource', 'agenceDestination', 'demandePar', 'validePar']);

                return response()->json([
                    'success' => true,
                    'data'    => $transfert,
                    'message' => $message,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e; // Propagation pour le bloc catch externe
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error'   => config('app.debug') ? $e->getTraceAsString() : null,
            ], 400);
        }
    }

    public function updateStatus(Request $request)
    {
        $id = $request->id;
        $newStatus = $request->newStatus;

        if (str_starts_with($id, 'transfert_')) {
            $transfertId = str_replace('transfert_', '', $id);
            $transfert = Transfert::findOrFail($transfertId);

            if ($newStatus === 'recu') {
                $transfert->recevoir(Auth::id());
            } elseif ($newStatus === 'en_transit') {
                $transfert->expedier(Auth::id());
            }

            return response()->json(['message' => 'Statut du transfert mis à jour']);
        }

        return response()->json(['message' => 'ID non valide'], 400);
    }

    public function statistiques(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Transfert::query();
        if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
            $query->forAgence($user->agence_id);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $query->count(),
                'en_transit' => (clone $query)->byStatut('expedie')->count(),
                'en_attente' => (clone $query)->byStatut('demande')->count(),
            ]
        ]);
    }

    public function getOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'statuts' => Transfert::getStatusDisponibles(),
                'types' => Transfert::getTypesDisponibles(),
            ]
        ]);
    }
}
