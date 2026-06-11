<?php

namespace App\DTO;

use Illuminate\Support\Arr;

class PanneDecisionDTO
{
    public function __construct(
        public readonly int $panneId,
        public readonly int $technicienId,
        public readonly string $decision, // repaired | remplacement | irrecuperable
        public readonly ?float $coutEstimatif = null,
        public readonly ?string $dateDiagnostic = null,
        public readonly ?string $commentaires = null,
    ) {
    }

    /**
     * Conversion payload API -> DTO
     */
    public static function fromArray(array $data): self
    {
        $decision = Arr::get($data, 'decision');

        return new self(
            panneId: (int) Arr::get($data, 'panne_id'),
            technicienId: (int) Arr::get($data, 'technicien_id'),
            decision: (string) $decision,
            coutEstimatif: Arr::has($data, 'cout_estime') ? (float) Arr::get($data, 'cout_estime') : null,
            dateDiagnostic: Arr::get($data, 'date_diagnostic'),
            commentaires: Arr::get($data, 'commentaires')
        );
    }
}

