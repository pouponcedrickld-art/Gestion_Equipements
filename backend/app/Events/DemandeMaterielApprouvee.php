<?php

namespace App\Events;

use App\Models\DemandeMateriel;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DemandeMaterielApprouvee
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public DemandeMateriel $demande;
    public User $chefAgence;

    /**
     * Create a new event instance.
     */
    public function __construct(DemandeMateriel $demande, User $chefAgence)
    {
        $this->demande = $demande;
        $this->chefAgence = $chefAgence;
    }
}
