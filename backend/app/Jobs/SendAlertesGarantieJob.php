<?php

namespace App\Jobs;

use App\Models\Equipement;
use App\Notifications\AlerteGarantieExpireNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendAlertesGarantieJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('=== Début du job d\'alerte de garantie ===');

        $targetDate = Carbon::now()->addDays(30)->toDateString();
        Log::info("Recherche d'équipements avec garantie_date_fin = {$targetDate}");

        $count = 0;

        Equipement::query()
            ->whereDate('garantie_date_fin', '=', $targetDate)
            ->with(['agenceActuelle.gestionnaireStock'])
            ->chunk(100, function ($equipements) use (&$count) {
                foreach ($equipements as $equipement) {
                    $gestionnaire = $equipement->agenceActuelle?->gestionnaireStock;

                    if ($gestionnaire) {
                        try {
                            Notification::send($gestionnaire, new AlerteGarantieExpireNotification($equipement));
                            Log::info("Notification envoyée à {$gestionnaire->name} pour l'équipement {$equipement->reference}");
                            $count++;
                        } catch (\Exception $e) {
                            Log::error("Erreur lors de l'envoi de la notification pour l'équipement {$equipement->id}: {$e->getMessage()}");
                        }
                    } else {
                        Log::warning("Aucun gestionnaire de stock trouvé pour l'équipement {$equipement->reference} (agence_id: {$equipement->agence_actuelle_id})");
                    }
                }
            });

        Log::info("=== Fin du job d'alerte de garantie. Notifications envoyées: {$count} ===");
    }
}
