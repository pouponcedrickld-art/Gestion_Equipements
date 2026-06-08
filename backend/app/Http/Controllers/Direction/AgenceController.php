<?php
namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AgenceController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Agence::class);
        return Agence::with(['sousAgences', 'responsable', 'gestionnaireStock'])->get();
    }

    public function store(Request $r)
    {
        Gate::authorize('create', Agence::class);
        $r->validate([
            'type' => 'required|in:generale,sous_agence',
            'nom' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:agences,id',
            'ville' => 'nullable|string|max:100',
            'adresse' => 'nullable|string',
            'code_postal' => 'nullable|string|max:20',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
        ]);
        return Agence::create($r->all());
    }

    public function show(Agence $agence)
    {
        Gate::authorize('view', $agence);
        return $agence->load(['sousAgences', 'responsable', 'gestionnaireStock', 'users']);
    }

    public function update(Request $r, Agence $agence)
    {
        Gate::authorize('update', $agence);
        $r->validate([
            'nom' => 'sometimes|string|max:255',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:20',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'responsable_id' => 'nullable|exists:users,id',
            'gestionnaire_stock_id' => 'nullable|exists:users,id',
            'statut' => 'sometimes|in:active,inactive',
        ]);
        $agence->update($r->only(['nom', 'adresse', 'ville', 'code_postal', 'telephone', 'email', 'responsable_id', 'gestionnaire_stock_id', 'statut']));
        return $agence->load(['responsable', 'gestionnaireStock']);
    }

    public function destroy(Agence $agence)
    {
        Gate::authorize('delete', $agence);
        if ($agence->sousAgences()->count() > 0) {
            return response()->json(['message' => 'Impossible de supprimer une agence ayant des sous-agences'], 422);
        }
        $agence->delete();
        return response()->noContent();
    }

    public function stats(Agence $agence)
    {
        Gate::authorize('view', $agence);
        return [
            'equipements_count' => $agence->equipements()->count(),
            'agents_count' => $agence->users()->whereHas('roles', fn($q) => $q->where('name', 'agent'))->count(),
            'pannes_actives' => $agence->equipements()->where('statut_global', 'en_panne')->count(),
            'transferts_en_cours' => $agence->transfertsDestination()->where('statut', 'expedie')->count(),
        ];
    }
}
