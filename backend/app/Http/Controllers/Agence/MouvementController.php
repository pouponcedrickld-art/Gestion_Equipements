<?php

namespace App\Http\Controllers\Agence;

use App\Http\Controllers\Controller;
use App\Models\Mouvement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MouvementController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Mouvement::with(['equipement', 'consommable', 'agent', 'user']);

        if (!$user->hasRole(['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance'])) {
            $query->whereHas('equipement', function($q) use ($user) {
                $q->where('agence_actuelle_id', $user->agence_id);
            })->orWhereHas('consommable', function($q) use ($user) {
                $q->whereHas('equipement', function($sq) use ($user) {
                    $sq->where('agence_actuelle_id', $user->agence_id);
                });
            });
        }

        if ($request->filled('type_mouvement')) {
            $query->where('type_mouvement', $request->type_mouvement);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('description', 'like', "%$search%")
                ->orWhereHas('equipement', function($q) use ($search) {
                    $q->where('nom', 'like', "%$search%")->orWhere('reference', 'like', "%$search%");
                });
        }

        $mouvements = $query->orderBy('date_mouvement', 'desc')->paginate(20);
        return response()->json($mouvements);
    }

    public function show(Mouvement $mouvement)
    {
        return response()->json($mouvement->load(['equipement', 'consommable', 'agent', 'user']));
    }
}
