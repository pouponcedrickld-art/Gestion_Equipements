<?php

namespace App\Services;

use App\Events\PanneDecisionIrrecuperable;
use App\Events\PanneDecisionReparee;
use App\Events\PanneDecisionRemplacementNecessaire;
use App\Models\Panne;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PanneDecisionService
{
    public function __construct(
        private readonly PanneWorkflowService $panneWorkflowService
    ) {
    }

    /**
     * Décision: panne réparée.
     */
    public function repare(Panne $panne, User $technicien, array $payload): Panne
    {
        return DB::transaction(function () use ($panne, $technicien, $payload) {
            $payload = $this->normalizePayload($payload);

            $panne = $this->panneWorkflowService->resoudre($panne, $technicien, [
                'diagnostic_technicien' => Arr::get($payload, 'diagnostic_technicien'),
                'action_realisee' => Arr::get($payload, 'action_realisee'),
                'cout_reparation' => Arr::get($payload, 'cout_reparation'),
                'decision_finale' => 'reparee',
                'solution' => Arr::get($payload, 'solution'),
                'commentaire' => Arr::get($payload, 'commentaires'),
            ]);

            event(new PanneDecisionReparee(
                panne: $panne,
                technicien: $technicien,
                coutEstimatif: Arr::get($payload, 'cout_reparation'),
                commentaires: Arr::get($payload, 'commentaires')
            ));

            return $panne;
        });
    }

    /**
     * Décision: remplacement nécessaire.
     * Mapping workflow: status finale = resolue.
     */
    public function remplacement(Panne $panne, User $technicien, array $payload): Panne
    {
        return DB::transaction(function () use ($panne, $technicien, $payload) {
            $payload = $this->normalizePayload($payload);

            $panne = $this->panneWorkflowService->resoudre($panne, $technicien, [
                'diagnostic_technicien' => Arr::get($payload, 'diagnostic_technicien'),
                'action_realisee' => Arr::get($payload, 'action_realisee'),
                'cout_reparation' => Arr::get($payload, 'cout_reparation'),
                'decision_finale' => 'remplacement',
                'solution' => Arr::get($payload, 'solution'),
                'commentaire' => Arr::get($payload, 'commentaires'),
            ]);

            event(new PanneDecisionRemplacementNecessaire(
                panne: $panne,
                technicien: $technicien,
                coutEstimatif: Arr::get($payload, 'cout_reparation'),
                commentaires: Arr::get($payload, 'commentaires')
            ));

            return $panne;
        });
    }

    /**
     * Décision: irrécupérable.
     */
    public function irrecuperable(Panne $panne, User $technicien, array $payload): Panne
    {
        return DB::transaction(function () use ($panne, $technicien, $payload) {
            $payload = $this->normalizePayload($payload);

            $panne = $this->panneWorkflowService->marquerIrrecuperable($panne, $technicien, [
                'diagnostic_technicien' => Arr::get($payload, 'diagnostic_technicien'),
                'action_realisee' => Arr::get($payload, 'action_realisee'),
                'cout_reparation' => Arr::get($payload, 'cout_reparation'),
                'decision_finale' => 'irrecuperable',
                'solution' => Arr::get($payload, 'solution'),
                'commentaire' => Arr::get($payload, 'commentaires'),
            ]);

            event(new PanneDecisionIrrecuperable(
                panne: $panne,
                technicien: $technicien,
                coutEstimatif: Arr::get($payload, 'cout_reparation'),
                commentaires: Arr::get($payload, 'commentaires')
            ));

            return $panne;
        });
    }

    private function normalizePayload(array $payload): array
    {
        // Compat API: cout_estimatif -> cout_reparation (champ workflow + historique)
        if (array_key_exists('cout_estimatif', $payload) && !array_key_exists('cout_reparation', $payload)) {
            $payload['cout_reparation'] = $payload['cout_estimatif'];
        }

        return $payload;
    }
}

