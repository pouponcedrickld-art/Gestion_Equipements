<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\DemandeMateriel;
use App\Http\Requests\Agence\StoreDemandeMaterielRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemandeAgenceController extends Controller
{
    /**
     * Affiche toutes les demandes (pour la direction).
     */
    public function index()
    {
        $demandes = DemandeMateriel::with(['equipement', 'chefAgence', 'agence'])
            ->latest()
            ->get();

        return response()->json($demandes);
    }

    /**
     * Traite une demande de matériel (Approuver / Refuser / Partiel).
     */
    public function traiter(Request $request, $id)
    {
        $request->validate([
            'decision' => 'required|in:Approuver,Refuser,Partiel',
            'quantite_validee' => 'required_if:decision,Approuver,Partiel|integer|min:1',
            'observations' => 'required_if:decision,Refuser|string|nullable',
        ]);

        $demande = DemandeMateriel::findOrFail($id);
        
        $statutMapping = [
            'Approuver' => 'approuvé',
            'Refuser' => 'rejeté',
            'Partiel' => 'approuvé', 
        ];

        $demande->update([
            'statut' => $statutMapping[$request->decision],
            'quantite' => $request->decision === 'Partiel' ? $request->quantite_validee : $demande->quantite,
            'observations' => $request->observations,
            'traite_par_id' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Demande traitée avec succès',
            'data' => $demande
        ]);
    }
}
