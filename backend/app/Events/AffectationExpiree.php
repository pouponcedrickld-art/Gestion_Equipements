<?php

namespace App\Events;

use App\Models\Affectation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AffectationExpiree
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Affectation $affectation;

    /**
     * Create a new event instance.
     */
    public function __construct(Affectation $affectation)
    {
        $this->affectation = $affectation;
    }
}
