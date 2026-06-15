<?php

namespace App\Http\Controllers\Agence;

use App\Http\Controllers\Controller;
use App\Models\Affectation;
use App\Models\Equipement;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AffectationController extends Controller
{
    /**
     * Liste des affectations de l'agence.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Affectation::with(['agent', 'equipement', 'affectePar'])
            ->whereHas('equipement', function($q) use ($user) {
                $q->where('agence_actuelle_id', $user->agence_id);
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('agent', function($sq) use ($search) {
                    $sq->where('nom', 'like', "%$search%")
                       ->orWhere('prenom', 'like', "%$search%");
                })->orWhereHas('equipement', function($sq) use ($search) {
                    $sq->where('nom', 'like', "%$search%")
                       ->orWhere('reference', 'like', "%$search%");
                });
            });
        }

        return response()->json($query->orderBy('date_affectation', 'desc')->paginate(15));
    }

    /**
     * Liste des affectations de l'utilisateur connecté (technicien ou agent).
     */
    public function mesAffectations()
    {
        $user = Auth::user();
        
        $query = Affectation::with(['agent', 'equipement', 'affectePar'])
            ->where('statut', 'active');

        // Si l'utilisateur a un agent lié, filtrer les affectations de cet agent
        if ($user->agent) {
            $query->where('agent_id', $user->agent->id);
        }

        return response()->json($query->orderBy('date_affectation', 'desc')->get());
    }

    /**
     * Créer une ou plusieurs affectations.
     */
    public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'equipement_ids' => 'required|array|min:1',
            'equipement_ids.*' => 'exists:equipements,id',
            'date_affectation' => 'required|date',
            'date_retour_prevu' => 'nullable|date|after_or_equal:date_affectation',
            'observations' => 'nullable|string',
            'etat_equipement' => 'required|in:nouveau,actif,en_maintenance,hors_service',
        ]);

        $user = Auth::user();
        $affectations = [];

        DB::transaction(function () use ($request, $user, &$affectations) {
            foreach ($request->equipement_ids as $equipementId) {
                $equipement = Equipement::findOrFail($equipementId);

                // Vérifier si l'équipement est déjà affecté
                if ($equipement->etat === 'actif' && $equipement->affectations()->where('statut', 'active')->exists()) {
                    throw new \Exception("L'équipement {$equipement->nom} est déjà affecté.");
                }

                $affectation = Affectation::create([
                    'agent_id' => $request->agent_id,
                    'equipement_id' => $equipementId,
                    'date_affectation' => $request->date_affectation,
                    'date_retour_prevu' => $request->date_retour_prevu,
                    'affecte_par' => $user->id,
                    'observations' => $request->observations,
                    'statut' => 'active',
                ]);

                // Mettre à jour l'état de l'équipement
                $equipement->update([
                    'etat' => $request->etat_equipement,
                    'statut_global' => 'en_service'
                ]);

                // Créer un mouvement
                $equipement->createMouvement(
                    'affectation',
                    "Affectation à l'agent ID: {$request->agent_id}. Obs: {$request->observations}",
                    $user->id
                );

                $affectations[] = $affectation;
            }
        });

        return response()->json([
            'success' => true,
            'message' => count($affectations) . ' équipement(s) affecté(s) avec succès.',
            'data' => $affectations
        ], 201);
    }

    /**
     * Retourner un équipement.
     */
    public function retour(Request $request, $id)
    {
        $request->validate([
            'date_retour_effectif' => 'required|date',
            'etat_retour' => 'required|in:bon,abime,non_fonctionnel',
            'observations' => 'nullable|string',
        ]);

        $affectation = Affectation::findOrFail($id);
        $user = Auth::user();

        DB::transaction(function () use ($affectation, $request, $user) {
            $affectation->update([
                'date_retour_effectif' => $request->date_retour_effectif,
                'etat_retour' => $request->etat_retour,
                'observations' => $affectation->observations . " | Retour: " . $request->observations,
                'statut' => 'retournee',
            ]);

            $equipement = $affectation->equipement;
            
            // Mapper l'état de retour vers l'état équipement
            $nouvelEtat = 'actif';
            if ($request->etat_retour === 'abime') $nouvelEtat = 'en_maintenance';
            if ($request->etat_retour === 'non_fonctionnel') $nouvelEtat = 'hors_service';

            $equipement->update([
                'etat' => $nouvelEtat,
                'statut_global' => $nouvelEtat === 'actif' ? 'en_stock_agence' : 'en_maintenance'
            ]);

            $equipement->createMouvement(
                'retour',
                "Retour d'affectation. État: {$request->etat_retour}. Obs: {$request->observations}",
                $user->id
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Équipement retourné avec succès.'
        ]);
    }
}
