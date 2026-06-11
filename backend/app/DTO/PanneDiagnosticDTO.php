<?php

namespace App\DTO;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PanneDiagnosticDTO
{
    public function __construct(
        public readonly int $panneId,
        public readonly int $technicienId,
        public readonly string $diagnostic,
        public readonly ?float $coutEstime,
        public readonly ?string $dateDiagnostic,
        public readonly ?string $commentaires,
        public readonly ?string $decision,
    ) {
        if ($coutEstime !== null && $coutEstime < 0) {
            throw new InvalidArgumentException('coutEstime doit être >= 0');
        }
    }

    public static function fromArray(array $data): self
    {
        // dateDiagnostic peut être omise, on la stockera côté workflow.
        return new self(
            panneId: (int) Arr::get($data, 'panne_id'),
            technicienId: (int) Arr::get($data, 'technicien_id'),
            diagnostic: (string) Arr::get($data, 'diagnostic_technicien'),
            coutEstime: Arr::has($data, 'cout_estime') ? (float) Arr::get($data, 'cout_estime') : (Arr::has($data, 'cout_reparation') ? (float) Arr::get($data, 'cout_reparation') : null),
            dateDiagnostic: Arr::get($data, 'date_diagnostic'),
            commentaires: Arr::get($data, 'commentaires'),
            decision: Arr::get($data, 'decision'),
        );
    }
}

