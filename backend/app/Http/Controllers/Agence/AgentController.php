<?php

namespace App\Http\Controllers\Agence;

use App\Http\Controllers\Controller;
use App\Models\Agent;

class AgentController extends Controller
{
    /**
     * Liste des agents.
     */
    public function index()
    {
        return response()->json(Agent::orderBy('nom')->get());
    }
}
