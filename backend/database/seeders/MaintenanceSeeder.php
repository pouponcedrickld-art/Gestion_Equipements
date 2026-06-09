<?php

namespace Database\Seeders;

use App\Models\Maintenance;
use App\Models\Equipement;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer quelques équipements existants
        $equipements = Equipement::limit(10)->get();
        
        if ($equipements->isEmpty()) {
            $this->command->warn('Aucun équipement trouvé. Veuillez d\'abord exécuter le seeder des équipements.');
            return;
        }

        // Récupérer des utilisateurs pour les techniciens
        $users = User::limit(5)->get();

        $this->command->info('Création de maintenances de test pour juin 2026...');

        // Maintenances pour juin 2026
        $maintenances = [
            [
                'equipement_id' => $equipements[0]->id,
                'type_maintenance' => 'préventif',
                'date_prevue' => '2026-06-08 09:00:00',
                'responsable' => 'Sophie Lambert',
                'statut' => 'terminee',
                'date_debut' => '2026-06-08 09:00:00',
                'date_fin' => '2026-06-08 10:30:00',
                'cout' => 150.00,
                'observations' => 'Maintenance trimestrielle effectuée avec succès',
                'diagnostic' => 'RAS - Équipement en bon état',
            ],
            [
                'equipement_id' => $equipements[1]->id ?? $equipements[0]->id,
                'type_maintenance' => 'préventif',
                'date_prevue' => '2026-06-10 14:00:00',
                'responsable' => 'Jean Dupont',
                'statut' => 'planifiee',
                'cout' => 200.00,
                'observations' => 'Vérification complète du système',
            ],
            [
                'equipement_id' => $equipements[2]->id ?? $equipements[0]->id,
                'type_maintenance' => 'correctif',
                'date_prevue' => '2026-06-12 10:30:00',
                'responsable' => 'Marie Martin',
                'statut' => 'en_cours',
                'date_debut' => '2026-06-12 10:30:00',
                'cout' => 450.00,
                'observations' => 'Réparation écran endommagé',
                'diagnostic' => 'Écran cassé suite à une chute',
            ],
            [
                'equipement_id' => $equipements[3]->id ?? $equipements[0]->id,
                'type_maintenance' => 'préventif',
                'date_prevue' => '2026-06-15 08:30:00',
                'responsable' => 'Pierre Dubois',
                'statut' => 'planifiee',
                'cout' => 180.00,
                'observations' => 'Nettoyage et mise à jour logicielle',
            ],
            [
                'equipement_id' => $equipements[4]->id ?? $equipements[0]->id,
                'type_maintenance' => 'correctif',
                'date_prevue' => '2026-06-15 15:00:00',
                'responsable' => 'Luc Bernard',
                'statut' => 'planifiee',
                'cout' => 320.00,
                'observations' => 'Remplacement batterie défectueuse',
            ],
            [
                'equipement_id' => $equipements[5]->id ?? $equipements[0]->id,
                'type_maintenance' => 'préventif',
                'date_prevue' => '2026-06-18 11:00:00',
                'responsable' => 'Anne Petit',
                'statut' => 'planifiee',
                'cout' => 150.00,
                'observations' => 'Maintenance semestrielle',
            ],
            [
                'equipement_id' => $equipements[6]->id ?? $equipements[0]->id,
                'type_maintenance' => 'préventif',
                'date_prevue' => '2026-06-20 09:00:00',
                'responsable' => 'Thomas Roux',
                'statut' => 'planifiee',
                'cout' => 175.00,
                'observations' => 'Vérification et calibration',
            ],
            [
                'equipement_id' => $equipements[7]->id ?? $equipements[0]->id,
                'type_maintenance' => 'correctif',
                'date_prevue' => '2026-06-22 13:30:00',
                'responsable' => 'Claire Moreau',
                'statut' => 'planifiee',
                'cout' => 380.00,
                'observations' => 'Réparation connecteur USB',
            ],
            [
                'equipement_id' => $equipements[8]->id ?? $equipements[0]->id,
                'type_maintenance' => 'préventif',
                'date_prevue' => '2026-06-25 10:00:00',
                'responsable' => 'Marc Simon',
                'statut' => 'planifiee',
                'cout' => 160.00,
                'observations' => 'Maintenance préventive standard',
            ],
            [
                'equipement_id' => $equipements[9]->id ?? $equipements[0]->id,
                'type_maintenance' => 'préventif',
                'date_prevue' => '2026-06-28 14:30:00',
                'responsable' => 'Julie Blanc',
                'statut' => 'planifiee',
                'cout' => 190.00,
                'observations' => 'Révision complète avant fermeture mensuelle',
            ],
        ];

        foreach ($maintenances as $maintenanceData) {
            // Ajouter un technicien si disponible
            if ($users->isNotEmpty()) {
                $maintenanceData['technicien_id'] = $users->random()->id;
            }

            Maintenance::create($maintenanceData);
        }

        $this->command->info('✓ ' . count($maintenances) . ' maintenances créées avec succès pour juin 2026');
        $this->command->info('Maintenances réparties sur plusieurs dates du mois pour tester le calendrier');
    }
}
