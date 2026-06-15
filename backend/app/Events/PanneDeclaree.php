<?php

namespace App\Events;

use App\Models\Panne;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class PanneDeclaree
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Panne $panne,
        public readonly User $actor,
    ) {
    }
}
