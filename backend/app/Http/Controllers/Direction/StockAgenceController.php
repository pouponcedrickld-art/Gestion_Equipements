<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\StockAgence;
use Illuminate\Http\Request;

class StockAgenceController extends Controller
{
    /**
     * Afficher le stock de toutes les agences (pour la direction)
     */
    public function index(Request $request)
    {
        $query = StockAgence::with(['agence', 'equipement', 'categorie']);

        // Filtre par agence
        if ($request->has('agence_id')) {
            $query->where('agence_id', $request->agence_id);
        }

        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('equipement', function($q) use ($search) {
                $q->where('nom', 'like', '%' . $search . '%')
                  ->orWhere('marque', 'like', '%' . $search . '%');
            });
        }

        $stock = $query->paginate(10);

        return response()->json($stock);
    }

    /**
     * Afficher le stock d'une agence spécifique
     */
    public function showByAgence($agenceId)
    {
        $stock = StockAgence::where('agence_id', $agenceId)
            ->with(['agence', 'equipement', 'categorie'])
            ->paginate(10);

        return response()->json($stock);
    }
}
