<?php

namespace App\Console\Commands;

use App\Models\Equipement;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

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
    protected $description = 'Check for equipment with expiring warranties and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expiring warranties...');

        // Find equipment where warranty expires in 30 days or less
        $expiryThreshold = Carbon::now()->addDays(30);

        $equipements = Equipement::whereNotNull('date_fin_garantie')
            ->where('date_fin_garantie', '<=', $expiryThreshold)
            ->where('date_fin_garantie', '>=', Carbon::now())
            ->get();

        $this->info("Found {$equipements->count()} equipment with expiring warranties.");

        foreach ($equipements as $equipement) {
            // Get relevant users (super admins, gestionnaires, chef agence)
            $users = User::whereHas('roles', function($q) {
                $q->whereIn('name', ['super_admin', 'gestionnaire_stock_general', 'chef_agence']);
            })->where(function($q) use ($equipement) {
                $q->where('agence_id', $equipement->agence_actuelle_id)
                  ->orWhereHas('roles', function($rq) {
                      $rq->whereIn('name', ['super_admin', 'gestionnaire_stock_general']);
                  });
            })->get();

            foreach ($users as $user) {
                NotificationService::sendNotification(
                    user: $user,
                    type: 'garantie_proche_expiration',
                    title: 'Garantie proche expiration',
                    message: "La garantie de l'équipement {$equipement->nom} ({$equipement->reference}) expire le {$equipement->date_fin_garantie->format('d/m/Y')}.",
                    data: ['equipement_id' => $equipement->id],
                    channels: ['in_app', 'email']
                );
            }

            $this->info("Notification sent for equipment: {$equipement->nom}");
        }

        $this->info('Done!');
    }
}
