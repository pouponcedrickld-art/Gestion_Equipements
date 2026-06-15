<?php
// =============================================
// FICHIER : DashboardAgenceController.php
// RÔLE : Contrôleur qui gère le dashboard de l'application
// Fournit toutes les statistiques et données pour le tableau de bord
// =============================================

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
use Illuminate\Support\Facades\Cache;

class DashboardAgenceController extends Controller
{
    // =============================================
    // MÉTHODE PRINCIPALE : Récupère toutes les stats du dashboard
    // =============================================
    public function index(Request $r)
    {
        // Récupère l'utilisateur connecté
        $user = $r->user();
        // Vérifie si l'utilisateur a un rôle global (peut voir toutes les agences)
        $isGlobal = $user->hasRole(['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance']);
        // Récupère l'ID de l'agence sélectionnée (si présente dans les paramètres de la requête)
        $agenceId = $r->query('agence_id');

        // Crée une clé de cache unique pour les stats
        $cacheKey = 'dashboard_stats_' . ($isGlobal ? 'global' : $user->agence_id) . ($agenceId ? '_' . $agenceId : '');
        // Récupère les stats depuis le cache (valides 5 minutes = 300 secondes)
        $stats = Cache::remember($cacheKey, 300, function () use ($isGlobal, $user, $agenceId) {
            $stats = [];

            // Si l'utilisateur est global (peut voir toutes les agences)
            if ($isGlobal) {
                // Fonction helper pour ajouter le filtre par agence aux requêtes
                $baseQuery = function ($query) use ($agenceId) {
                    if ($agenceId) {
                        $query->where('agence_actuelle_id', $agenceId);
                    }
                    return $query;
                };

                // Récupère toutes les statistiques globales
                $stats = [
                    'total_equipements' => Equipement::when($agenceId, function ($q, $agenceId) {
                        return $q->where('agence_actuelle_id', $agenceId);
                    })->count(),
                    'en_stock_general' => $baseQuery(Equipement::query())->where('statut_global', 'en_stock_general')->count(),
                    'en_transit' => $baseQuery(Equipement::query())->where('statut_global', 'en_transit')->count(),
                    'affectes' => $baseQuery(Equipement::query())->where('statut_global', 'affecte')->count(),
                    'en_panne' => $baseQuery(Equipement::query())->where('statut_global', 'en_panne')->count(),
                    'en_maintenance' => $baseQuery(Equipement::query())->where('statut_global', 'en_maintenance')->count(),
                    'transferts_en_cours' => Transfert::when($agenceId, function ($q, $agenceId) {
                        $q->where('agence_source_id', $agenceId)->orWhere('agence_destination_id', $agenceId);
                    })->whereIn('statut', ['demande', 'approuve', 'expedie'])->count(),
                    'demandes_en_attente' => DemandeMateriel::when($agenceId, function ($q, $agenceId) {
                        $q->where('agence_id', $agenceId);
                    })->where('statut', 'en attente')->count(),
                    'pannes_non_resolues' => Panne::when($agenceId, function ($q, $agenceId) {
                        $q->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $agenceId));
                    })->whereIn('statut', ['declaree', 'transmise_maintenance', 'en_diagnostic', 'en_cours'])->count(),
                    'maintenances_planifiees' => Maintenance::when($agenceId, function ($q, $agenceId) {
                        $q->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $agenceId));
                    })->where('statut', 'planifiee')->count(),
                    'agences_count' => $agenceId ? 1 : Agence::where('type', 'sous_agence')->count(),
                    'agents_actifs' => \App\Models\Agent::when($agenceId, function ($q, $agenceId) {
                        $q->whereHas('user', fn($u) => $u->where('agence_id', $agenceId));
                    })->where('statut', 'actif')->count(),
                    
                    // Nouveaux indicateurs
                    'nombre_pannes' => Panne::when($agenceId, function ($q, $agenceId) {
                        $q->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $agenceId));
                    })->count(),
                    'taux_resolution' => $this->calculateResolutionRate($agenceId),
                    'cout_maintenance' => $this->calculateMaintenanceCost($agenceId),
                    'equipements_en_maintenance' => Maintenance::when($agenceId, function ($q, $agenceId) {
                        $q->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $agenceId));
                    })->where('statut', 'en_cours')->count(),
                    'equipements_irrecuperables' => Equipement::when($agenceId, function ($q, $agenceId) {
                        $q->where('agence_actuelle_id', $agenceId);
                    })->where('statut_global', 'reforme')->count(),
                    'temps_moyen_reparation' => $this->calculateAvgRepairTime($agenceId),
                    'garanties_expirant' => Equipement::when($agenceId, function ($q, $agenceId) {
                        $q->where('agence_actuelle_id', $agenceId);
                    })->where('garantie_date_fin', '>=', now())->where('garantie_date_fin', '<=', now()->addDays(30))->count(),
                ];

                // Équipements répartis par agence
                $stats['equipements_par_agence'] = Agence::when($agenceId, function ($q, $agenceId) {
                    return $q->where('id', $agenceId);
                })->where('type', 'sous_agence')
                    ->withCount('equipementsActuels as total')
                    ->get(['id', 'nom']);
                
                // Équipements répartis par catégorie
                $stats['equipements_par_categorie'] = \App\Models\Categorie::withCount([
                    'equipements' => function ($q) use ($agenceId) {
                        if ($agenceId) {
                            $q->where('agence_actuelle_id', $agenceId);
                        }
                    }
                ])->get(['id', 'nom', 'equipements_count']);

                // Activité récente des 7 derniers jours
                $stats['activite_recente'] = [
                    'transferts' => Transfert::when($agenceId, function ($q, $agenceId) {
                        $q->where('agence_source_id', $agenceId)->orWhere('agence_destination_id', $agenceId);
                    })->where('created_at', '>=', now()->subDays(7))->count(),
                    'affectations' => Affectation::when($agenceId, function ($q, $agenceId) {
                        $q->where('agence_id', $agenceId);
                    })->where('created_at', '>=', now()->subDays(7))->count(),
                    'pannes' => Panne::when($agenceId, function ($q, $agenceId) {
                        $q->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $agenceId));
                    })->where('created_at', '>=', now()->subDays(7))->count(),
                    'maintenances' => Maintenance::when($agenceId, function ($q, $agenceId) {
                        $q->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $agenceId);
                    })->where('created_at', '>=', now()->subDays(7))->count(),
                ];

                // Données pour les graphiques
                // Tendance des pannes sur les 14 derniers jours
                $stats['pannes_trend'] = Panne::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                    ->when($agenceId, function ($q, $agenceId) {
                        $q->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $agenceId));
                    })
                    ->where('created_at', '>=', now()->subDays(14))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                    
                // Tendance des maintenances sur les 14 derniers jours
                $stats['maintenances_trend'] = Maintenance::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                    ->when($agenceId, function ($q, $agenceId) {
                        $q->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $agenceId));
                    })
                    ->where('created_at', '>=', now()->subDays(14))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                    
                // Répartition des pannes par statut
                $stats['pannes_statut'] = Panne::select('statut', DB::raw('count(*) as count'))
                    ->when($agenceId, function ($q, $agenceId) {
                        $q->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $agenceId));
                    })
                    ->groupBy('statut')
                    ->get();
            } 
            // Si l'utilisateur est dans une agence spécifique (pas global)
            else {
                $aid = $agenceId ?? $user->agence_id;
                $stats = [
                    'total_equipements' => Equipement::where('agence_actuelle_id', $aid)->count(),
                    'en_stock_local' => Equipement::where('agence_actuelle_id', $aid)->where('statut_global', 'en_stock_local')->count(),
                    'affectes' => Equipement::where('agence_actuelle_id', $aid)->where('statut_global', 'affecte')->count(),
                    'en_panne' => Equipement::where('agence_actuelle_id', $aid)->where('statut_global', 'en_panne')->count(),
                    'en_maintenance' => Equipement::where('agence_actuelle_id', $aid)->where('statut_global', 'en_maintenance')->count(),
                    'transferts_en_cours' => Transfert::where(function($q) use ($aid) {
                            $q->where('agence_source_id', $aid)->orWhere('agence_destination_id', $aid);
                        })->whereIn('statut', ['demande', 'approuve', 'expedie'])->count(),
                    'demandes_en_attente' => DemandeMateriel::where('agence_id', $aid)->where('statut', 'en attente')->count(),
                    'pannes_a_traiter' => Panne::whereHas('equipement', fn($q) => $q->where('agence_actuelle_id', $aid))->where('statut', 'declaree')->count(),
                    'agents_count' => \App\Models\Agent::whereHas('user', fn($q) => $q->where('agence_id', $aid))->count(),
                    
                    // Nouveaux indicateurs
                    'nombre_pannes' => Panne::whereHas('equipement', fn($q) => $q->where('agence_actuelle_id', $aid))->count(),
                    'taux_resolution' => $this->calculateResolutionRate($aid),
                    'cout_maintenance' => $this->calculateMaintenanceCost($aid),
                    'equipements_en_maintenance' => Maintenance::whereHas('equipement', fn($q) => $q->where('agence_actuelle_id', $aid))->where('statut', 'en_cours')->count(),
                    'equipements_irrecuperables' => Equipement::where('agence_actuelle_id', $aid)->where('statut_global', 'reforme')->count(),
                    'temps_moyen_reparation' => $this->calculateAvgRepairTime($aid),
                    'garanties_expirant' => Equipement::where('agence_actuelle_id', $aid)->where('garantie_date_fin', '>=', now())->where('garantie_date_fin', '<=', now()->addDays(30))->count(),
                ];

                // Équipements par catégorie pour une agence
                $stats['equipements_par_categorie'] = \App\Models\Categorie::withCount(['equipements' => fn($q) => $q->where('agence_actuelle_id', $aid)])->get(['id', 'nom', 'equipements_count']);
                
                // Tendance des pannes
                $stats['pannes_trend'] = Panne::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                    ->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $aid))
                    ->where('created_at', '>=', now()->subDays(14))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                    
                // Tendance des maintenances
                $stats['maintenances_trend'] = Maintenance::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                    ->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $aid))
                    ->where('created_at', '>=', now()->subDays(14))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                    
                // Répartition des pannes par statut
                $stats['pannes_statut'] = Panne::select('statut', DB::raw('count(*) as count'))
                    ->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $aid))
                    ->groupBy('statut')
                    ->get();
            }

            return $stats;
        });

        // Retourne les données en JSON
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->getRoleNames()->first(),
                'agence' => $user->agence,
            ],
            'stats' => $stats,
            'agences' => Agence::where('type', 'sous_agence')->get(['id', 'nom']),
        ]);
    }
    
    // =============================================
    // MÉTHODES AUXILIAIRES
    // =============================================
    
    // Calcule le taux de résolution des pannes
    private function calculateResolutionRate($agenceId = null)
    {
        $total = Panne::when($agenceId, function ($q, $agenceId) {
            $q->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $agenceId));
        })->count();
        
        if ($total === 0) return 0;
        
        $resolu = Panne::when($agenceId, function ($q, $agenceId) {
            $q->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $agenceId));
        })->whereIn('statut', ['resolue', 'cloturee'])->count();
        
        return round(($resolu / $total) * 100, 2);
    }
    
    // Calcule le coût total des maintenances
    private function calculateMaintenanceCost($agenceId = null)
    {
        return Maintenance::when($agenceId, function ($q, $agenceId) {
            $q->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $agenceId));
        })->sum('cout');
    }
    
    // Calcule le temps moyen de réparation (en heures)
    private function calculateAvgRepairTime($agenceId = null)
    {
        $pannes = Panne::when($agenceId, function ($q, $agenceId) {
            $q->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $agenceId));
        })->whereNotNull('date_resolution')->whereNotNull('date_declaration')->get();
        
        if ($pannes->count() === 0) return 0;
        
        $totalHours = 0;
        foreach ($pannes as $panne) {
            $totalHours += $panne->date_declaration->diffInHours($panne->date_resolution);
        }
        
        return round($totalHours / $pannes->count(), 2);
    }
}
