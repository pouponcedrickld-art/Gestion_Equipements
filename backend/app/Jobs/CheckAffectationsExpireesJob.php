<?php

namespace App\Jobs;

use App\Models\Affectation;
use App\Events\AffectationExpiree;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckAffectationsExpireesJob implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $affectationsExpirees = Affectation::where('statut', 'active')
            ->whereNotNull('date_retour_prevu')
            ->where('date_retour_prevu', '<', now())
            ->get();

        foreach ($affectationsExpirees as $affectation) {
            $affectation->update(['statut' => 'expiree']);
            event(new AffectationExpiree($affectation));
        }
    }
}
