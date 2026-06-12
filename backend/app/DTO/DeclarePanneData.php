<?php

namespace App\DTO;

use App\Models\Equipement;
use App\Models\User;

class DeclarePanneData
{
    public function __construct(
        public readonly int $equipementId,
        public readonly int $agentId,
        public readonly string $description,
        public readonly string $niveauGravite,
        public readonly ?array $photos = null,
    ) {
    }
}
