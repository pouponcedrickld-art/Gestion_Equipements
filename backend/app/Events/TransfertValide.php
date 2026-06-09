<?php

namespace App\Events;

use App\Models\Agence;
use App\Models\Equipement;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransfertValide
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Equipement $equipement;
    public Agence $agenceDestination;

    /**
     * Create a new event instance.
     */
    public function __construct(Equipement $equipement, Agence $agenceDestination)
    {
        $this->equipement = $equipement;
        $this->agenceDestination = $agenceDestination;
    }
}
