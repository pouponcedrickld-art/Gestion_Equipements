<?php

namespace App\Jobs;

use App\Models\Maintenance;
use App\Models\User;
use App\Notifications\AlerteMaintenancePrevue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendAlerteMaintenancePrevueJob implements ShouldQueue
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
    public function handle(): void
    {
        // Récupérer les maintenances planifiées dans les 24-48 heures
        $now = now();
        $start = $now->copy()->addHours(24);
        $end = $now->copy()->addHours(48);

        $maintenances = Maintenance::with('equipement')
            ->where('statut', 'planifiee')
            ->whereBetween('date_prevue', [$start, $end])
            ->get();

        // Récupérer les utilisateurs concernés (gestionnaires et techniciens)
        $users = User::whereIn('role', ['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance', 'gestionnaire_stock'])
            ->get();

        foreach ($maintenances as $maintenance) {
            Notification::send($users, new AlerteMaintenancePrevue($maintenance));
        }
    }
}
