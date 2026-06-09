<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Equipement;
use App\Models\Panne;

class RapportGlobalController extends Controller
{
    public function inventaire(Request $request)
    {
        $equipements = Equipement::with(['categorie', 'agenceActuelle', 'agenceProprietaire'])
            ->when($request->agence_id, fn($q) => $q->where('agence_actuelle_id', $request->agence_id))
            ->when($request->statut, fn($q) => $q->where('statut_global', $request->statut))
            ->get();

        return response()->json([
            'data' => $equipements,
            'total' => $equipements->count()
        ]);
    }

    public function pannes(Request $request)
    {
        $pannes = Panne::with(['equipement', 'agence'])
            ->when($request->agence_id, fn($q) => $q->where('agence_id', $request->agence_id))
            ->when($request->gravite, fn($q) => $q->where('gravite', $request->gravite))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $pannes,
            'total' => $pannes->count()
        ]);
    }

    public function export(Request $request, $type)
    {
        // TODO: Implémenter l'export (PDF, Excel, etc.)
        return response()->json([
            'message' => 'Export ' . $type . ' en cours de développement'
        ]);
    }
}
