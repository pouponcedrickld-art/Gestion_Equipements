<?php

namespace App\Console\Commands;

use App\Services\GarantieAlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiringWarranties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:check-expiring-warranties';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifier les garanties expirant et envoyer les alertes';

    /**
     * Execute the console command.
     */
    public function handle(GarantieAlertService $alertService): void
    {
        $this->info('Vérification des garanties expirant...');
        Log::info('Début de la commande notifications:check-expiring-warranties');

        try {
            $alertService->verifierEtEnvoyerAlertes();
            $this->info('Vérification terminée avec succès !');
            Log::info('Fin de la commande notifications:check-expiring-warranties avec succès');
        } catch (\Exception $e) {
            $this->error('Erreur lors de la vérification: ' . $e->getMessage());
            Log::error('Erreur lors de la vérification des garanties', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
