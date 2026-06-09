<?php

namespace App\Http\Controllers\Agence;

use App\Http\Controllers\Controller;
use App\Models\DemandeMateriel;
use App\Http\Requests\Agence\StoreDemandeMaterielRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemandeMaterielController extends Controller
{
    /**
     * Affiche la liste des demandes de matériel.
     */
    public function index()
    {
        $user = Auth::user();
        $query = DemandeMateriel::with(['equipement', 'chefAgence', 'agence']);

        // Si c'est un rôle global, il voit tout
        if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance'])) {
            // Sinon, il ne voit que les demandes de son agence
            $query->where('agence_id', $user->agence_id);
        }

        $demandes = $query->latest()->get();

        return response()->json($demandes);
    }

    /**
     * Enregistre une nouvelle demande de matériel.
     */
    public function store(StoreDemandeMaterielRequest $request)
    {
        try {
            $user = Auth::user();

            if (!$user->agence_id) {
                return response()->json([
                    'message' => 'Votre compte n\'est rattaché à aucune agence. Veuillez contacter l\'administrateur.'
                ], 403);
            }

            // Crée la demande avec les données validées
            $demande = DemandeMateriel::create([
                'agence_id' => $user->agence_id,
                'chef_agence_id' => $user->id,
                'equipement_id' => $request->equipement_id,
                'quantite' => $request->quantite,
                'urgence' => $request->urgence,
                'motif' => $request->motif,
                'date_souhaitee' => $request->date_souhaitee,
                'statut' => 'en attente', // Statut par défaut
            ]);

            return response()->json([
                'message' => 'Demande de matériel créée avec succès',
                'data' => $demande->load('equipement')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur technique lors de la création : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Met à jour une demande existante.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantite' => 'required|integer|min:1',
            'urgence' => 'required|in:Basse,Moyenne,Haute',
            'motif' => 'required|string',
            'date_souhaitee' => 'required|date',
            'statut' => 'sometimes|in:en attente,approuvé,rejeté'
        ]);

        $demande = DemandeMateriel::findOrFail($id);
        $demande->update($request->all());

        return response()->json([
            'message' => 'Demande mise à jour avec succès',
            'data' => $demande->load('equipement')
        ]);
    }

    /**
     * Supprime une demande.
     */
    public function destroy($id)
    {
        $demande = DemandeMateriel::findOrFail($id);
        $demande->delete();

        return response()->json([
            'message' => 'Demande supprimée avec succès'
        ]);
    }
}
