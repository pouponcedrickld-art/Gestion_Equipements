<?php

namespace App\Http\Controllers;

use App\Services\RapportService;
use Illuminate\Http\Request;

class RapportController extends Controller
{
    protected RapportService $rapportService;

    public function __construct(RapportService $rapportService)
    {
        $this->rapportService = $rapportService;
    }

    /**
     * Get inventory by agency report data
     */
    public function inventaireParAgence(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'required|exists:agences,id',
            'categorie_id' => 'nullable|exists:categories,id',
            'statut_global' => 'nullable|string'
        ]);

        $data = $this->rapportService->getInventaireParAgence($validated['agence_id'], $validated);

        return response()->json($data);
    }

    /**
     * Download inventory by agency PDF
     */
    public function downloadInventaireParAgence(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'required|exists:agences,id',
            'categorie_id' => 'nullable|exists:categories,id',
            'statut_global' => 'nullable|string'
        ]);

        $data = $this->rapportService->getInventaireParAgence($validated['agence_id'], $validated);

        return $this->rapportService->genererPDF(
            'pdf.inventaire-par-agence',
            $data,
            'inventaire-agence-' . $data['agence']->nom . '.pdf'
        );
    }

    /**
     * Preview inventory by agency PDF
     */
    public function previewInventaireParAgence(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'required|exists:agences,id',
            'categorie_id' => 'nullable|exists:categories,id',
            'statut_global' => 'nullable|string'
        ]);

        $data = $this->rapportService->getInventaireParAgence($validated['agence_id'], $validated);

        return $this->rapportService->previewPDF('pdf.inventaire-par-agence', $data);
    }

    /**
     * Get assigned equipment report data
     */
    public function equipementsAffectes(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'nullable|exists:agences,id',
            'user_id' => 'nullable|exists:users,id'
        ]);

        $data = $this->rapportService->getEquipementsAffectes($validated);

        return response()->json($data);
    }

    /**
     * Download assigned equipment PDF
     */
    public function downloadEquipementsAffectes(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'nullable|exists:agences,id',
            'user_id' => 'nullable|exists:users,id'
        ]);

        $data = $this->rapportService->getEquipementsAffectes($validated);

        return $this->rapportService->genererPDF(
            'pdf.equipements-affectes',
            $data,
            'equipements-affectes.pdf'
        );
    }

    /**
     * Preview assigned equipment PDF
     */
    public function previewEquipementsAffectes(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'nullable|exists:agences,id',
            'user_id' => 'nullable|exists:users,id'
        ]);

        $data = $this->rapportService->getEquipementsAffectes($validated);

        return $this->rapportService->previewPDF('pdf.equipements-affectes', $data);
    }

    /**
     * Get equipment in breakdown report data
     */
    public function equipementsEnPanne(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'nullable|exists:agences,id',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut'
        ]);

        $data = $this->rapportService->getEquipementsEnPanne($validated);

        return response()->json($data);
    }

    /**
     * Download equipment in breakdown PDF
     */
    public function downloadEquipementsEnPanne(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'nullable|exists:agences,id',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut'
        ]);

        $data = $this->rapportService->getEquipementsEnPanne($validated);

        return $this->rapportService->genererPDF(
            'pdf.equipements-en-panne',
            $data,
            'equipements-en-panne.pdf'
        );
    }

    /**
     * Preview equipment in breakdown PDF
     */
    public function previewEquipementsEnPanne(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'nullable|exists:agences,id',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut'
        ]);

        $data = $this->rapportService->getEquipementsEnPanne($validated);

        return $this->rapportService->previewPDF('pdf.equipements-en-panne', $data);
    }

    /**
     * Get maintenances report data
     */
    public function maintenances(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'nullable|exists:agences,id',
            'type_maintenance' => 'nullable|string',
            'statut' => 'nullable|string',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut'
        ]);

        $data = $this->rapportService->getMaintenances($validated);

        return response()->json($data);
    }

    /**
     * Download maintenances PDF
     */
    public function downloadMaintenances(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'nullable|exists:agences,id',
            'type_maintenance' => 'nullable|string',
            'statut' => 'nullable|string',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut'
        ]);

        $data = $this->rapportService->getMaintenances($validated);

        return $this->rapportService->genererPDF(
            'pdf.maintenances',
            $data,
            'maintenances.pdf'
        );
    }

    /**
     * Preview maintenances PDF
     */
    public function previewMaintenances(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'nullable|exists:agences,id',
            'type_maintenance' => 'nullable|string',
            'statut' => 'nullable|string',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut'
        ]);

        $data = $this->rapportService->getMaintenances($validated);

        return $this->rapportService->previewPDF('pdf.maintenances', $data);
    }

    /**
     * Get losses and damages report data
     */
    public function pertesEtCasses(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'nullable|exists:agences,id',
            'type' => 'nullable|string',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut'
        ]);

        $data = $this->rapportService->getPertesEtCasses($validated);

        return response()->json($data);
    }

    /**
     * Download losses and damages PDF
     */
    public function downloadPertesEtCasses(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'nullable|exists:agences,id',
            'type' => 'nullable|string',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut'
        ]);

        $data = $this->rapportService->getPertesEtCasses($validated);

        return $this->rapportService->genererPDF(
            'pdf.pertes-et-casses',
            $data,
            'pertes-et-casses.pdf'
        );
    }

    /**
     * Preview losses and damages PDF
     */
    public function previewPertesEtCasses(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'nullable|exists:agences,id',
            'type' => 'nullable|string',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut'
        ]);

        $data = $this->rapportService->getPertesEtCasses($validated);

        return $this->rapportService->previewPDF('pdf.pertes-et-casses', $data);
    }

    /**
     * Get complete audit report data
     */
    public function auditComplet(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'nullable|exists:agences,id'
        ]);

        $data = $this->rapportService->getAuditComplet($validated['agence_id'] ?? null);

        return response()->json($data);
    }

    /**
     * Download complete audit PDF
     */
    public function downloadAuditComplet(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'nullable|exists:agences,id'
        ]);

        $data = $this->rapportService->getAuditComplet($validated['agence_id'] ?? null);

        $filename = $data['agence'] ? 'audit-agence-' . $data['agence']->nom . '.pdf' : 'audit-complet.pdf';

        return $this->rapportService->genererPDF(
            'pdf.audit-complet',
            $data,
            $filename
        );
    }

    /**
     * Preview complete audit PDF
     */
    public function previewAuditComplet(Request $request)
    {
        $validated = $request->validate([
            'agence_id' => 'nullable|exists:agences,id'
        ]);

        $data = $this->rapportService->getAuditComplet($validated['agence_id'] ?? null);

        return $this->rapportService->previewPDF('pdf.audit-complet', $data);
    }
}
