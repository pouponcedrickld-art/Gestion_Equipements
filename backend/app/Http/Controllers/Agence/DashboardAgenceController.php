<?php
namespace App\Http\Controllers\Agence;

use App\Http\Controllers\Controller;
use App\Models\Equipement;
use App\Models\Panne;
use App\Models\Transfert;
use App\Models\DemandeMateriel;
use App\Models\Agence;
use App\Models\Affectation;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardAgenceController extends Controller
{
    public function index(Request $r)
    {
        $user = $r->user();
        $isGlobal = $user->hasRole(['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance']);

        if ($isGlobal) {
            $stats = [
                'total_equipements' => Equipement::count(),
                'en_stock_general' => Equipement::where('statut_global', 'en_stock_general')->count(),
                'en_transit' => Equipement::where('statut_global', 'en_transit')->count(),
                'affectes' => Equipement::where('statut_global', 'affecte')->count(),
                'en_panne' => Equipement::where('statut_global', 'en_panne')->count(),
                'en_maintenance' => Equipement::where('statut_global', 'en_maintenance')->count(),
                'transferts_en_cours' => Transfert::whereIn('statut', ['demande', 'approuve', 'expedie'])->count(),
                'demandes_en_attente' => DemandeMateriel::where('statut', 'en attente')->count(),
                'pannes_non_resolues' => Panne::whereIn('statut', ['declaree', 'transmise_maintenance', 'en_diagnostic'])->count(),
                'maintenances_planifiees' => Maintenance::where('statut', 'planifiee')->count(),
                'agences_count' => Agence::where('type', 'sous_agence')->count(),
                'agents_actifs' => \App\Models\Agent::where('statut', 'actif')->count(),
            ];

            $stats['equipements_par_agence'] = Agence::where('type', 'sous_agence')
                ->withCount('equipementsActuels as total')
                ->get(['id', 'nom']);
                
            $stats['equipements_par_categorie'] = \App\Models\Categorie::withCount('equipements')->get(['id', 'nom', 'equipements_count']);

            $stats['activite_recente'] = [
                'transferts' => Transfert::where('created_at', '>=', now()->subDays(7))->count(),
                'affectations' => Affectation::where('created_at', '>=', now()->subDays(7))->count(),
                'pannes' => Panne::where('created_at', '>=', now()->subDays(7))->count(),
                'maintenances' => Maintenance::where('created_at', '>=', now()->subDays(7))->count(),
            ];

            // Données pour les graphiques
            $stats['pannes_trend'] = Panne::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', now()->subDays(14))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        } else {
            $aid = $user->agence_id;
            $stats = [
                'total_equipements' => Equipement::where('agence_actuelle_id', $aid)->count(),
                'en_stock_local' => Equipement::where('agence_actuelle_id', $aid)->where('statut_global', 'en_stock_local')->count(),
                'affectes' => Equipement::where('agence_actuelle_id', $aid)->where('statut_global', 'affecte')->count(),
                'en_panne' => Equipement::where('agence_actuelle_id', $aid)->where('statut_global', 'en_panne')->count(),
                'transferts_en_cours' => Transfert::where(function($q) use ($aid) {
                        $q->where('agence_source_id', $aid)->orWhere('agence_destination_id', $aid);
                    })->whereIn('statut', ['demande', 'approuve', 'expedie'])->count(),
                'demandes_en_attente' => DemandeMateriel::where('agence_id', $aid)->where('statut', 'en attente')->count(),
                'pannes_a_traiter' => Panne::whereHas('equipement', fn($q) => $q->where('agence_actuelle_id', $aid))->where('statut', 'declaree')->count(),
                'agents_count' => \App\Models\Agent::whereHas('user', fn($q) => $q->where('agence_id', $aid))->count(),
            ];

            $stats['equipements_par_categorie'] = \App\Models\Categorie::withCount(['equipements' => fn($q) => $q->where('agence_actuelle_id', $aid)])->get(['id', 'nom', 'equipements_count']);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->getRoleNames()->first(),
                'agence' => $user->agence,
            ],
            'stats' => $stats,
        ]);
    }
}
