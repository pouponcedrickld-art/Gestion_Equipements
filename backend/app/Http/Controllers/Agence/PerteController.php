<?php

namespace App\Http\Controllers\Agence;

use App\Http\Controllers\Controller;
use App\Models\Perte;
use App\Models\Equipement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerteController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Perte::with(['equipement', 'agent', 'validePar']);

        if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance'])) {
            $query->whereHas('equipement', function($q) use ($user) {
                $q->where('agence_actuelle_id', $user->agence_id);
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        return response()->json($query->orderBy('date_declaration', 'desc')->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'equipement_id' => 'required|exists:equipements,id',
            'agent_id' => 'required|exists:agents,id',
            'type' => 'required|in:perte,vol,casse',
            'description' => 'required|string',
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($request, $user) {
            $perte = Perte::create([
                'equipement_id' => $request->equipement_id,
                'agent_id' => $request->agent_id,
                'type' => $request->type,
                'date_declaration' => now(),
                'description' => $request->description,
                'statut' => 'en attente',
            ]);

            $equipement = Equipement::findOrFail($request->equipement_id);
            $equipement->update(['statut_global' => 'perdu']);
            $equipement->createMouvement(
                'perte',
                "Déclaration de {$request->type} par agent ID: {$request->agent_id}. Description: {$request->description}",
                $user->id
            );
        });

        return response()->json(['message' => 'Perte déclarée avec succès'], 201);
    }

    public function show(Perte $perte)
    {
        return response()->json($perte->load(['equipement', 'agent', 'validePar']));
    }

    public function update(Request $request, Perte $perte)
    {
        $request->validate([
            'statut' => 'required|in:en attente,validé,clôturé',
            'description' => 'nullable|string',
        ]);

        $user = Auth::user();

        $perte->update([
            'statut' => $request->statut,
            'description' => $request->description ?? $perte->description,
            'valide_par' => $request->statut !== 'en attente' ? $user->id : $perte->valide_par,
            'date_validation' => $request->statut !== 'en attente' ? now() : $perte->date_validation,
        ]);

        return response()->json(['message' => 'Perte mise à jour avec succès']);
    }

    public function destroy(Perte $perte)
    {
        $perte->delete();
        return response()->json(['message' => 'Perte supprimée avec succès']);
    }
}
