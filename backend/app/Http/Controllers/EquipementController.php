<?php

namespace App\Http\Controllers;

use App\Models\Equipement;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipementController extends Controller
{
    /**
     * Affiche la liste des équipements.
     */
    public function index()
    {
        try {
            // On récupère les équipements disponibles pour les demandes
            // En priorité ceux du stock général pour les demandes de sous-agences
            $equipements = Equipement::with('categorie')
                ->whereIn('statut_global', ['en_stock_general', 'en_stock_local'])
                ->get();
            
            return response()->json($equipements);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
