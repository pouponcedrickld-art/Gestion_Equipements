<?php

namespace App\Events;

use App\Models\Panne;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PanneCloturee implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Panne $panne,
        public readonly User $technicienOuActeur,
        public readonly ?float $coutEstimatif = null,
        public readonly ?string $commentaires = null,
        public readonly ?string $solution = null,
        public readonly ?string $decisionFinale = null,
    ) {
    }

    public function broadcastOn(): array
    {
        return [];
    }
}

