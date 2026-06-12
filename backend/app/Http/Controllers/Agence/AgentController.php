<?php

namespace App\Http\Controllers\Agence;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use App\Http\Requests\Agence\StoreAgentRequest;

class AgentController extends Controller
{
    public function index()
    {
        return Agent::with(['user'])->get();
    }

    public function available()
    {
        return Agent::whereNull('user_id')->get();
    }

    public function postes()
    {
        $defaultPostes = [
            'Agent de Terrain',
            'Superviseur',
            'Technicien de Maintenance',
            'Chef de Service',
            'Comptable',
            'Gestionnaire de Stock',
            'Secrétaire',
            'Chauffeur'
        ];

        $existingPostes = Agent::whereNotNull('poste')
            ->distinct()
            ->pluck('poste')
            ->toArray();

        return array_values(array_unique(array_merge($defaultPostes, $existingPostes)));
    }

    public function store(StoreAgentRequest $r)
    {
        return Agent::create($r->validated());
    }

    public function show(Agent $agent)
    {
        return $agent->load(['user', 'affectations', 'mouvements', 'pannes', 'pertes']);
    }

    public function update(Request $r, Agent $agent)
    {
        $r->validate([
            'matricule' => 'nullable|string|max:50|unique:agents,matricule,' . $agent->id,
            'nom' => 'sometimes|string|max:255',
            'prenom' => 'sometimes|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255|unique:agents,email,' . $agent->id,
            'poste' => 'nullable|string|max:255',
            'statut' => 'sometimes|in:actif,inactif',
            'photo' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $agent->update($r->all());
        return $agent->load('user');
    }

    public function destroy(Agent $agent)
    {
        $agent->delete();
        return response()->noContent();
    }
}
