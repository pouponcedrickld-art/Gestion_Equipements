<?php

namespace App\Http\Controllers\Agence;

use App\Http\Controllers\Controller;
use App\Models\Panne;
use App\Models\Equipement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PanneController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Panne::with(['equipement', 'agent', 'gestionnaireStock', 'technicien', 'maintenances']);

        if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance'])) {
            $query->whereHas('equipement', function($q) use ($user) {
                $q->where('agence_actuelle_id', $user->agence_id);
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('niveau_gravite')) {
            $query->where('niveau_gravite', $request->niveau_gravite);
        }

        return response()->json($query->orderBy('date_declaration', 'desc')->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'equipement_id' => 'required|exists:equipements,id',
            'agent_id' => 'required|exists:agents,id',
            'description' => 'required|string',
            'niveau_gravite' => 'required|in:basse,moyenne,haute,critique',
            'photos' => 'nullable|array',
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($request, $user) {
            $panne = Panne::create([
                'equipement_id' => $request->equipement_id,
                'agent_id' => $request->agent_id,
                'date_declaration' => now(),
                'description' => $request->description,
                'niveau_gravite' => $request->niveau_gravite,
                'photos' => $request->photos ?? [],
                'statut' => 'déclarée',
                'gestionnaire_stock_id' => $user->id,
            ]);

            $equipement = Equipement::findOrFail($request->equipement_id);
            $equipement->update([
                'etat' => 'en_maintenance',
                'statut_global' => 'en_panne',
            ]);
            $equipement->createMouvement(
                'panne',
                "Panne déclarée par agent ID: {$request->agent_id}. Gravité: {$request->niveau_gravite}. Description: {$request->description}",
                $user->id
            );
        });

        return response()->json(['message' => 'Panne déclarée avec succès'], 201);
    }

    public function show(Panne $panne)
    {
        return response()->json($panne->load(['equipement', 'agent', 'gestionnaireStock', 'technicien', 'maintenances']));
    }

    public function update(Request $request, Panne $panne)
    {
        $request->validate([
            'description' => 'nullable|string',
            'niveau_gravite' => 'nullable|in:basse,moyenne,haute,critique',
            'statut' => 'nullable|in:déclarée,en cours,réparée,irrécupérable,remplacée',
            'technicien_id' => 'nullable|exists:users,id',
            'diagnostic_technicien' => 'nullable|string',
            'action_realisee' => 'nullable|string',
            'cout_reparation' => 'nullable|numeric|min:0',
            'date_resolution' => 'nullable|date',
            'decision_finale' => 'nullable|string',
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($request, $panne, $user) {
            $panne->update(array_filter($request->all()));

            if ($request->filled('statut')) {
                $equipement = $panne->equipement;
                $newStatus = match($request->statut) {
                    'réparée' => ['etat' => 'actif', 'statut_global' => 'en_stock_agence'],
                    'irrécupérable' => ['etat' => 'hors_service', 'statut_global' => 'reformé'],
                    'remplacée' => ['etat' => 'actif', 'statut_global' => 'en_stock_agence'],
                    'en cours' => ['etat' => 'en_maintenance', 'statut_global' => 'en_panne'],
                    default => null,
                };
                if ($newStatus) {
                    $equipement->update($newStatus);
                    if ($request->statut === 'réparée') {
                        $equipement->createMouvement(
                            'reparation',
                            "Panne résolue. Action: {$request->action_realisee} - Coût: {$request->cout_reparation}",
                            $user->id
                        );
                    }
                }
            }
        });

        return response()->json(['message' => 'Panne mise à jour avec succès']);
    }

    public function transmettreMaintenance(Request $request, $id)
    {
        $request->validate([
            'technicien_id' => 'required|exists:users,id',
        ]);

        $panne = Panne::findOrFail($id);
        $panne->update([
            'statut' => 'en cours',
            'technicien_id' => $request->technicien_id,
        ]);

        return response()->json(['message' => 'Panne transmise au technicien']);
    }

    public function diagnostiquer(Request $request, $id)
    {
        $request->validate([
            'diagnostic_technicien' => 'required|string',
        ]);

        $panne = Panne::findOrFail($id);
        $panne->update([
            'diagnostic_technicien' => $request->diagnostic_technicien,
        ]);

        return response()->json(['message' => 'Diagnostic enregistré']);
    }

    public function destroy(Panne $panne)
    {
        $panne->delete();
        return response()->json(['message' => 'Panne supprimée avec succès']);
    }
}
