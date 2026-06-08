<?php

namespace App\Http\Controllers;

use App\Models\Transfert;
use App\Models\DemandeMateriel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransfertController extends Controller
{
    /**
     * Liste des transferts et demandes approuvées pour le kanban.
     */
    public function index()
    {
        // 1. Récupérer les demandes approuvées (A expédier)
        $a_expedier = DemandeMateriel::with(['equipement', 'agence', 'chefAgence'])
            ->where('statut', 'approuvé')
            ->get()
            ->map(function($demande) {
                return [
                    'id' => 'demande_' . $demande->id,
                    'real_id' => $demande->id,
                    'type' => 'demande',
                    'nom_materiel' => $demande->equipement->nom,
                    'agence' => $demande->agence->nom,
                    'quantite' => $demande->quantite,
                    'urgence' => $demande->urgence,
                    'date' => $demande->date_souhaitee,
                ];
            });

        // 2. Récupérer les transferts en cours (En transit)
        $en_transit = Transfert::with(['equipement', 'agenceDestination'])
            ->where('statut', 'expedie')
            ->get()
            ->map(function($transfert) {
                return [
                    'id' => 'transfert_' . $transfert->id,
                    'real_id' => $transfert->id,
                    'type' => 'transfert',
                    'nom_materiel' => $transfert->equipement->nom,
                    'agence' => $transfert->agenceDestination->nom,
                    'quantite' => $transfert->quantite,
                    'date' => $transfert->date_expedition,
                ];
            });

        // 3. Récupérer les transferts terminés (Destination annex)
        $recu = Transfert::with(['equipement', 'agenceDestination'])
            ->where('statut', 'recu')
            ->latest()
            ->take(20)
            ->get()
            ->map(function($transfert) {
                return [
                    'id' => 'transfert_' . $transfert->id,
                    'real_id' => $transfert->id,
                    'type' => 'transfert',
                    'nom_materiel' => $transfert->equipement->nom,
                    'agence' => $transfert->agenceDestination->nom,
                    'quantite' => $transfert->quantite,
                    'date' => $transfert->date_reception,
                ];
            });

        return response()->json([
            'a_expedier' => $a_expedier,
            'en_transit' => $en_transit,
            'recu' => $recu
        ]);
    }

    /**
     * Met à jour le statut d'un transfert ou transforme une demande en transfert.
     */
    public function updateStatus(Request $request)
    {
        $id = $request->id; // Format: 'demande_1' ou 'transfert_1'
        $newStatus = $request->newStatus; // 'en_transit' ou 'recu'

        if (str_starts_with($id, 'demande_')) {
            $demandeId = str_replace('demande_', '', $id);
            $demande = DemandeMateriel::findOrFail($demandeId);

            // Créer le transfert officiel
            $transfert = Transfert::create([
                'equipement_id' => $demande->equipement_id,
                'agence_source_id' => 1, // Siège Social
                'agence_destination_id' => $demande->agence_id,
                'type_transfert' => 'livraison_generale',
                'demande_par_id' => $demande->chef_agence_id,
                'valide_par_id' => Auth::id(),
                'date_demande' => $demande->created_at,
                'date_expedition' => now(),
                'statut' => 'expedie',
                'quantite' => $demande->quantite,
                'observations' => $demande->motif
            ]);

            // Marquer la demande comme expédiée pour qu'elle sorte de la colonne "A expédier"
            $demande->update(['statut' => 'expédié']);

            return response()->json(['message' => 'Transfert généré et expédié', 'transfert' => $transfert]);
        }

        if (str_starts_with($id, 'transfert_')) {
            $transfertId = str_replace('transfert_', '', $id);
            $transfert = Transfert::findOrFail($transfertId);

            if ($newStatus === 'recu') {
                $transfert->update([
                    'statut' => 'recu',
                    'date_reception' => now()
                ]);
            }

            return response()->json(['message' => 'Statut du transfert mis à jour']);
        }

        return response()->json(['message' => 'ID non valide'], 400);
    }
}
