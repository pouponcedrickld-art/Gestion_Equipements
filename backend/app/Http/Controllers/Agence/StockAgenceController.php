<?php

namespace App\Http\Controllers\Agence;

use App\Http\Controllers\Controller;
use App\Models\StockAgence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockAgenceController extends Controller
{
    /**
     * Afficher le stock de l'agence connectée
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $agenceId = $user->agence_id;

        if (!$agenceId) {
            return response()->json(['error' => 'Agence non trouvée'], 404);
        }

        $query = StockAgence::where('agence_id', $agenceId)
            ->with(['equipement', 'categorie']);

        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('equipement', function($q) use ($search) {
                $q->where('nom', 'like', '%' . $search . '%')
                  ->orWhere('marque', 'like', '%' . $search . '%')
                  ->orWhere('modele', 'like', '%' . $search . '%');
            });
        }

        // Filtre par catégorie
        if ($request->has('categorie_id')) {
            $query->where('categorie_id', $request->categorie_id);
        }

        $stock = $query->paginate(10);

        return response()->json($stock);
    }

    /**
     * Afficher le détail d'un stock
     */
    public function show($id)
    {
        $user = Auth::user();
        $agenceId = $user->agence_id;

        $stock = StockAgence::where('agence_id', $agenceId)
            ->where('id', $id)
            ->with(['equipement', 'categorie'])
            ->first();

        if (!$stock) {
            return response()->json(['error' => 'Stock non trouvé'], 404);
        }

        return response()->json($stock);
    }
}
