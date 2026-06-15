<?php

namespace App\Jobs;

use App\Services\GarantieAlertService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAlertesGarantieJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
    public function handle(GarantieAlertService $alertService): void
    {
        Log::info('Exécution du job SendAlertesGarantieJob');

        try {
            $alertService->verifierEtEnvoyerAlertes();
            Log::info('Job SendAlertesGarantieJob terminé avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'exécution du job SendAlertesGarantieJob', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
