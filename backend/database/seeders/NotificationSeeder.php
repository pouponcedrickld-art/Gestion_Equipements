<?php

// database/seeders/NotificationSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            echo "⚠️ Aucun utilisateur trouvé.\n";
            return;
        }

        $admin = $users->first();
        $gestionnaire = $users->where('email', 'gestionnaire@gestpark.local')->first();
        $technicien = $users->where('email', 'technicien@gestpark.local')->first();

        $notifications = [
            [
                'user_id' => $admin->id,
                'type' => 'transfert',
                'titre' => 'Nouveau transfert approuvé',
                'message' => 'Le transfert #3 (PDA Zebra MC3300 #2) a été reçu par l\'agence de Lomé',
                'data' => json_encode(['transfert_id' => 3, 'equipement' => 'PDA-002']),
                'lu' => true,
                'canal' => 'in_app',
            ],
            [
                'user_id' => $gestionnaire?->id ?? $admin->id,
                'type' => 'panne',
                'titre' => 'Nouvelle panne déclarée',
                'message' => 'La tablette Zebra ET51 (TAB-001) a été déclarée en panne par l\'agent Jean Dupont',
                'data' => json_encode(['panne_id' => 1, 'equipement_id' => 5, 'gravite' => 'majeure']),
                'lu' => false,
                'canal' => 'email',
            ],
            [
                'user_id' => $technicien?->id ?? $admin->id,
                'type' => 'maintenance',
                'titre' => 'Maintenance assignée',
                'message' => 'Vous avez été assigné à la maintenance corrective de la tablette Zebra ET51',
                'data' => json_encode(['maintenance_id' => 2, 'equipement_id' => 5]),
                'lu' => false,
                'canal' => 'in_app',
            ],
            [
                'user_id' => $admin->id,
                'type' => 'demande_materiel',
                'titre' => 'Nouvelle demande de matériel',
                'message' => 'Le chef d\'agence de Lomé a demandé 2 scanners supplémentaires',
                'data' => json_encode(['demande_id' => 1, 'agence' => 'Lomé', 'quantite' => 2]),
                'lu' => false,
                'canal' => 'in_app',
            ],
            [
                'user_id' => $gestionnaire?->id ?? $admin->id,
                'type' => 'perte',
                'titre' => 'Perte validée',
                'message' => 'La perte du PDA Zebra MC3300 (PDA-001) a été validée - clôture du dossier',
                'data' => json_encode(['perte_id' => 1, 'equipement_id' => 1]),
                'lu' => true,
                'canal' => 'email',
            ],
            [
                'user_id' => $technicien?->id ?? $admin->id,
                'type' => 'garantie',
                'titre' => 'Alerte fin de garantie',
                'message' => 'La garantie du PDA Zebra MC3300 #2 expire dans 30 jours (15/01/2026)',
                'data' => json_encode(['equipement_id' => 2, 'date_fin' => '2026-01-15']),
                'lu' => false,
                'canal' => 'email',
            ],
            [
                'user_id' => $admin->id,
                'type' => 'retard',
                'titre' => 'Retour d\'affectation en retard',
                'message' => 'L\'affectation #3 (Tablette Zebra ET51) aurait dû être retournée il y a 5 jours',
                'data' => json_encode(['affectation_id' => 3, 'jours_retard' => 5]),
                'lu' => false,
                'canal' => 'in_app',
            ],
            [
                'user_id' => $gestionnaire?->id ?? $admin->id,
                'type' => 'affectation',
                'titre' => 'Nouvelle affectation',
                'message' => 'Le smartphone Cat S62 Pro a été affecté à l\'agent Marie Koffi',
                'data' => json_encode(['affectation_id' => 2, 'agent_id' => 2, 'equipement_id' => 3]),
                'lu' => true,
                'canal' => 'in_app',
            ],
        ];

        foreach ($notifications as $notificationData) {
            Notification::create($notificationData);
        }

        echo "✅ " . count($notifications) . " notifications de test créées !\n";
    }
}