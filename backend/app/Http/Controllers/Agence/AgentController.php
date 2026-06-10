<?php

namespace App\Http\Controllers\Agence;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index()
    {
        return Agent::with(['user'])->get();
    }

    public function store(Request $r)
    {
        $r->validate([
            'matricule' => 'required|string|max:50|unique:agents,matricule',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'direction' => 'nullable|string|max:255',
            'service' => 'nullable|string|max:255',
            'poste' => 'nullable|string|max:255',
            'statut' => 'sometimes|in:actif,inactif',
            'photo' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
        ]);

        return Agent::create($r->all());
    }

    public function show(Agent $agent)
    {
        return $agent->load(['user', 'affectations', 'mouvements', 'pannes', 'pertes']);
    }

    public function update(Request $r, Agent $agent)
    {
        $r->validate([
            'matricule' => 'sometimes|string|max:50|unique:agents,matricule,' . $agent->id,
            'nom' => 'sometimes|string|max:255',
            'prenom' => 'sometimes|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'direction' => 'nullable|string|max:255',
            'service' => 'nullable|string|max:255',
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
