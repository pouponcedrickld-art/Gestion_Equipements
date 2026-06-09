<?php

namespace App\Services;

use App\Models\Equipement;
use App\Models\Affectation;
use App\Models\Transfert;
use App\Models\Panne;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RapportService
{
    public function getGlobalStats()
    {
        return [
            'total_equipements' => Equipement::count(),
            'equipements_par_statut' => Equipement::select('statut_global', DB::raw('count(*) as total'))
                ->groupBy('statut_global')
                ->get(),
            'equipements_par_etat' => Equipement::select('etat', DB::raw('count(*) as total'))
                ->groupBy('etat')
                ->get(),
            'affectations_actives' => Affectation::where('statut', 'active')->count(),
            'transferts_en_cours' => Transfert::whereIn('statut', ['demande', 'approuve', 'expedie'])->count(),
            'valeur_parc' => Equipement::sum('prix_achat'),
        ];
    }

    public function getEquipementsReport($filters = [])
    {
        $query = Equipement::with(['categorie', 'agenceActuelle', 'agenceProprietaire']);

        if (isset($filters['agence_id'])) {
            $query->where('agence_actuelle_id', $filters['agence_id']);
        }

        if (isset($filters['categorie_id'])) {
            $query->where('categorie_id', $filters['categorie_id']);
        }

        if (isset($filters['statut'])) {
            $query->where('statut_global', $filters['statut']);
        }

        return $query->latest()->get();
    }

    public function getMouvementsStats($period = 'month')
    {
        $startDate = Carbon::now();
        if ($period === 'month') $startDate->subMonth();
        elseif ($period === 'year') $startDate->subYear();

        return DB::table('mouvements')
            ->select('type_mouvement', DB::raw('count(*) as total'), DB::raw('DATE(date_mouvement) as date'))
            ->where('date_mouvement', '>=', $startDate)
            ->groupBy('type_mouvement', 'date')
            ->orderBy('date')
            ->get();
    }

    public function getPannesStats($filters = [])
    {
        $query = Panne::with(['equipement', 'agent']);
        
        if (isset($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }
        
        return $query->latest()->get();
    }
}
