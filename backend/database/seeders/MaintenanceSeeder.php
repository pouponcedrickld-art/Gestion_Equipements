<?php

// database/seeders/MaintenanceSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Maintenance;
use App\Models\Equipement;
use App\Models\User;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        $equipements = Equipement::all();
        $technicien = User::role('technicien_maintenance')->first();
        $gestionnaire = User::role('gestionnaire_stock_general')->first();

        if ($equipements->isEmpty()) {
            echo "⚠️ Aucun équipement trouvé.\n";
            return;
        }

        $maintenances = [
            [
                'equipement_id' => $equipements->where('reference', 'PDA-001')->first()->id,
                'type_maintenance' => 'preventif',
                'date_prevue' => now()->addDays(7),
                'responsable' => $gestionnaire?->name ?? 'Gestionnaire Stock',
                'technicien_id' => $technicien?->id,
                'diagnostic' => 'Vérification générale - batterie, connectique, mise à jour firmware',
                'cout' => 0.00,
                'date_debut' => null,
                'date_fin' => null,
                'observations' => 'Maintenance trimestrielle planifiée',
                'statut' => 'planifiee',
            ],
            [
                'equipement_id' => $equipements->where('reference', 'TAB-001')->first()->id,
                'type_maintenance' => 'correctif',
                'date_prevue' => now()->subDays(10),
                'responsable' => $gestionnaire?->name ?? 'Gestionnaire Stock',
                'technicien_id' => $technicien?->id,
                'diagnostic' => 'RAS - Équipement en bon état après réparation',
                'cout' => 350.00,
                'date_debut' => now()->subDays(10)->setTime(9, 0),
                'date_fin' => now()->subDays(10)->setTime(10, 30),
                'observations' => 'Maintenance trimestrielle effectuée avec succès',
                'statut' => 'terminee',
            ],
            [
                'equipement_id' => $equipements->where('reference', 'SPH-001')->first()->id,
                'type_maintenance' => 'correctif',
                'date_prevue' => now()->subDays(3),
                'responsable' => $gestionnaire?->name ?? 'Gestionnaire Stock',
                'technicien_id' => $technicien?->id,
                'diagnostic' => 'Connecteur de charge et batterie à remplacer',
                'cout' => 120.00,
                'date_debut' => now()->subDays(3)->setTime(14, 0),
                'date_fin' => null,
                'observations' => 'En cours - pièces commandées',
                'statut' => 'en_cours',
            ],
        ];

        foreach ($maintenances as $maintenanceData) {
            if ($maintenanceData['equipement_id']) {
                Maintenance::create($maintenanceData);
            }
        }

        echo "✅ Maintenances de test créées avec succès !\n";
    }
}