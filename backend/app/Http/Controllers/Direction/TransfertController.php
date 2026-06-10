<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Transfert;
use App\Models\Equipement;
use App\Models\Agence;
use App\Models\DemandeMateriel;
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
            ->where('statut', 'en_attente_expedition')
            ->get()
            ->map(fn($t) => $this->mapForKanban($t, 'transfert'));

        // 2. En transit
        $en_transit = Transfert::with(['equipement', 'agenceDestination'])
            ->where('statut', 'en_transit')
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
        return [
            'id' => $type . '_' . $item->id,
            'real_id' => $item->id,
            'type' => $type,
            'nom_materiel' => $item->equipement->nom ?? ($item->equipement->marque . ' ' . $item->equipement->modele),
            'agence' => $item->agenceDestination->nom,
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
                'statut' => 'brouillon',
                'quantite' => 1,
            ]));

            return response()->json([
                'success' => true,
                'data' => $transfert,
                'message' => 'Transfert créé (Brouillon)'
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
            $transfert = Transfert::findOrFail($id);
            $transfert->refuser($request->user()->id, $request->observations);
            return response()->json(['success' => true, 'message' => 'Transfert annulé']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function expedier(Request $request, $id): JsonResponse
    {
        try {
            $transfert = Transfert::findOrFail($id);
            $transfert->expedier($request->user()->id);
            return response()->json(['success' => true, 'message' => 'Équipement expédié (En transit)']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function recevoir(Request $request, $id): JsonResponse
    {
        try {
            $transfert = Transfert::findOrFail($id);
            $transfert->recevoir($request->user()->id);
            return response()->json(['success' => true, 'message' => 'Transfert reçu, stock agence mis à jour']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
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
                'en_transit' => $query->byStatut('en_transit')->count(),
                'en_attente' => $query->byStatut('en_attente_expedition')->count(),
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
