<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Equipement;
use App\Models\Agence;
use App\Models\Panne;
use App\Models\Maintenance;
use App\Models\Affectation;
use App\Models\Perte;
use Illuminate\Support\Carbon;

class RapportController extends Controller
{
    /**
     * Rapport inventaire par agence
     */
    public function inventaireParAgence(Request $request)
    {
        $agenceId = $request->query('agence_id');
        
        $query = Equipement::with(['categorie', 'agenceActuelle']);
        
        if ($agenceId) {
            $query->where('agence_actuelle_id', $agenceId);
        }
        
        $equipements = $query->orderBy('agence_actuelle_id')->get();
        
        $grouped = $equipements->groupBy('agence_actuelle_id');
        
        return response()->json([
            'data' => $grouped->map(function ($items, $agenceId) {
                return [
                    'agence' => $items->first()->agenceActuelle,
                    'equipements' => $items,
                    'total' => $items->count(),
                ];
            })->values(),
            'total' => $equipements->count(),
        ]);
    }

    /**
     * Téléchargement PDF inventaire par agence
     */
    public function downloadInventaireParAgence(Request $request)
    {
        $data = $this->inventaireParAgence($request)->getData(true);
        $data['date'] = now()->format('d/m/Y');
        
        $pdf = Pdf::loadView('pdf.inventaire-par-agence', $data);
        
        return $pdf->download('inventaire-par-agence-' . now()->format('YmdHis') . '.pdf');
    }

    /**
     * Aperçu PDF inventaire par agence
     */
    public function previewInventaireParAgence(Request $request)
    {
        $data = $this->inventaireParAgence($request)->getData(true);
        $data['date'] = now()->format('d/m/Y');
        
        $pdf = Pdf::loadView('pdf.inventaire-par-agence', $data);
        
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf');
    }

    /**
     * Rapport équipements affectés
     */
    public function equipementsAffectes(Request $request)
    {
        $agenceId = $request->query('agence_id');
        
        $query = Affectation::with(['agent', 'equipement', 'agence'])
            ->where('statut', 'active');
        
        if ($agenceId) {
            $query->where('agence_id', $agenceId);
        }
        
        $affectations = $query->get();
        
        return response()->json([
            'data' => $affectations,
            'total' => $affectations->count(),
        ]);
    }

    public function downloadEquipementsAffectes(Request $request)
    {
        $data = $this->equipementsAffectes($request)->getData(true);
        $data['date'] = now()->format('d/m/Y');
        
        $pdf = Pdf::loadView('pdf.equipements-affectes', $data);
        
        return $pdf->download('equipements-affectes-' . now()->format('YmdHis') . '.pdf');
    }

    public function previewEquipementsAffectes(Request $request)
    {
        $data = $this->equipementsAffectes($request)->getData(true);
        $data['date'] = now()->format('d/m/Y');
        
        $pdf = Pdf::loadView('pdf.equipements-affectes', $data);
        
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf');
    }

    /**
     * Rapport équipements en panne
     */
    public function equipementsEnPanne(Request $request)
    {
        $agenceId = $request->query('agence_id');
        
        $query = Panne::with(['equipement', 'equipement.agenceActuelle'])
            ->whereIn('statut', ['declaree', 'transmise_maintenance', 'en_diagnostic', 'en_cours']);
        
        if ($agenceId) {
            $query->whereHas('equipement', fn($q) => $q->where('agence_actuelle_id', $agenceId));
        }
        
        $pannes = $query->get();
        
        return response()->json([
            'data' => $pannes,
            'total' => $pannes->count(),
        ]);
    }

    public function downloadEquipementsEnPanne(Request $request)
    {
        $data = $this->equipementsEnPanne($request)->getData(true);
        $data['date'] = now()->format('d/m/Y');
        
        $pdf = Pdf::loadView('pdf.equipements-en-panne', $data);
        
        return $pdf->download('equipements-en-panne-' . now()->format('YmdHis') . '.pdf');
    }

    public function previewEquipementsEnPanne(Request $request)
    {
        $data = $this->equipementsEnPanne($request)->getData(true);
        $data['date'] = now()->format('d/m/Y');
        
        $pdf = Pdf::loadView('pdf.equipements-en-panne', $data);
        
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf');
    }

    /**
     * Rapport maintenances
     */
    public function maintenances(Request $request)
    {
        $agenceId = $request->query('agence_id');
        $periode = $request->query('periode', 'month');
        
        $query = Maintenance::with(['equipement', 'equipement.agenceActuelle', 'panne']);
        
        if ($agenceId) {
            $query->whereHas('equipement', fn($q) => $q->where('agence_actuelle_id', $agenceId));
        }
        
        if ($periode) {
            $date = now();
            if ($periode === 'month') {
                $query->whereMonth('date_debut', $date->month)->whereYear('date_debut', $date->year);
            } elseif ($periode === 'quarter') {
                $query->whereBetween('date_debut', [$date->startOfQuarter(), $date->endOfQuarter()]);
            } elseif ($periode === 'year') {
                $query->whereYear('date_debut', $date->year);
            }
        }
        
        $maintenances = $query->orderBy('date_debut', 'desc')->get();
        
        $coutTotal = $maintenances->sum('cout');
        
        return response()->json([
            'data' => $maintenances,
            'total' => $maintenances->count(),
            'cout_total' => $coutTotal,
        ]);
    }

    public function downloadMaintenances(Request $request)
    {
        $data = $this->maintenances($request)->getData(true);
        $data['date'] = now()->format('d/m/Y');
        
        $pdf = Pdf::loadView('pdf.maintenances', $data);
        
        return $pdf->download('maintenances-' . now()->format('YmdHis') . '.pdf');
    }

    public function previewMaintenances(Request $request)
    {
        $data = $this->maintenances($request)->getData(true);
        $data['date'] = now()->format('d/m/Y');
        
        $pdf = Pdf::loadView('pdf.maintenances', $data);
        
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf');
    }

    /**
     * Rapport pertes et casses
     */
    public function pertesEtCasses(Request $request)
    {
        $agenceId = $request->query('agence_id');
        
        $query = Perte::with(['equipement', 'equipement.agenceActuelle', 'user']);
        
        if ($agenceId) {
            $query->whereHas('equipement', fn($q) => $q->where('agence_actuelle_id', $agenceId));
        }
        
        $pertes = $query->orderBy('date_perte', 'desc')->get();
        
        return response()->json([
            'data' => $pertes,
            'total' => $pertes->count(),
        ]);
    }

    public function downloadPertesEtCasses(Request $request)
    {
        $data = $this->pertesEtCasses($request)->getData(true);
        $data['date'] = now()->format('d/m/Y');
        
        $pdf = Pdf::loadView('pdf.pertes-et-casses', $data);
        
        return $pdf->download('pertes-et-casses-' . now()->format('YmdHis') . '.pdf');
    }

    public function previewPertesEtCasses(Request $request)
    {
        $data = $this->pertesEtCasses($request)->getData(true);
        $data['date'] = now()->format('d/m/Y');
        
        $pdf = Pdf::loadView('pdf.pertes-et-casses', $data);
        
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf');
    }

    /**
     * Rapport audit complet
     */
    public function auditComplet(Request $request)
    {
        $agenceId = $request->query('agence_id');
        
        $stats = [
            'equipements_total' => Equipement::when($agenceId, fn($q) => $q->where('agence_actuelle_id', $agenceId))->count(),
            'en_stock' => Equipement::when($agenceId, fn($q) => $q->where('agence_actuelle_id', $agenceId))->whereIn('statut_global', ['en_stock_general', 'en_stock_local'])->count(),
            'affectes' => Equipement::when($agenceId, fn($q) => $q->where('agence_actuelle_id', $agenceId))->where('statut_global', 'affecte')->count(),
            'en_panne' => Equipement::when($agenceId, fn($q) => $q->where('agence_actuelle_id', $agenceId))->where('statut_global', 'en_panne')->count(),
            'pannes_total' => Panne::when($agenceId, fn($q) => $q->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $agenceId)))->count(),
            'maintenances_total' => Maintenance::when($agenceId, fn($q) => $q->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $agenceId)))->count(),
            'maintenances_cout' => Maintenance::when($agenceId, fn($q) => $q->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $agenceId)))->sum('cout'),
            'pertes_total' => Perte::when($agenceId, fn($q) => $q->whereHas('equipement', fn($eq) => $eq->where('agence_actuelle_id', $agenceId)))->count(),
        ];
        
        return response()->json([
            'stats' => $stats,
            'agences' => Agence::when($agenceId, fn($q) => $q->where('id', $agenceId))->get(),
            'date' => now()->format('d/m/Y H:i:s'),
        ]);
    }

    public function downloadAuditComplet(Request $request)
    {
        $data = $this->auditComplet($request)->getData(true);
        
        $pdf = Pdf::loadView('pdf.audit-complet', $data);
        
        return $pdf->download('audit-complet-' . now()->format('YmdHis') . '.pdf');
    }

    public function previewAuditComplet(Request $request)
    {
        $data = $this->auditComplet($request)->getData(true);
        
        $pdf = Pdf::loadView('pdf.audit-complet', $data);
        
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf');
    }
}
