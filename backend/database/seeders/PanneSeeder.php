<?php

// database/seeders/PanneSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Panne;
use App\Models\Equipement;
use App\Models\Agent;
use App\Models\User;

class PanneSeeder extends Seeder
{
    public function run(): void
    {
        $equipement = Equipement::where('reference', 'TAB-001')->first();
        $agent = Agent::where('matricule', 'AGT-001')->first();
        $gestionnaire = User::role('gestionnaire_stock')->first();
        $technicien = User::role('technicien_maintenance')->first();

        if (!$equipement || !$agent) {
            echo "⚠️ Données manquantes pour les pannes.\n";
            return;
        }

        $pannes = [
            [
                'equipement_id' => $equipement->id,
                'agent_id' => $agent->id,
                'gestionnaire_stock_id' => $gestionnaire?->id,
                'technicien_id' => $technicien?->id,
                'date_declaration' => now()->subDays(10),
                'description' => 'L\'écran de la tablette ne répond plus au tactile après une chute',
                'niveau_gravite' => 'majeure',
                'photos' => json_encode(['panne_001_1.jpg', 'panne_001_2.jpg']),
                'diagnostic_technicien' => 'Détérioration du digitizer - nécessite remplacement',
                'action_realisee' => 'Remplacement du module écran tactile',
                'cout_reparation' => 350.00,
                'statut' => 'resolue',
                'date_resolution' => now()->subDays(2),
                'solution' => 'Écran remplacé, tests fonctionnels OK',
                'decision_finale' => 'repare',
            ],
            [
                'equipement_id' => Equipement::where('reference', 'PDA-002')->first()?->id,
                'agent_id' => Agent::where('matricule', 'AGT-002')->first()?->id,
                'gestionnaire_stock_id' => $gestionnaire?->id,
                'technicien_id' => null,
                'date_declaration' => now()->subDays(5),
                'description' => 'Le scanner intégré ne lit plus les codes-barres',
                'niveau_gravite' => 'mineure',
                'photos' => null,
                'diagnostic_technicien' => null,
                'action_realisee' => null,
                'cout_reparation' => null,
                'statut' => 'declaree',
                'date_resolution' => null,
                'solution' => null,
                'decision_finale' => 'en_attente',
            ],
            [
                'equipement_id' => Equipement::where('reference', 'SPH-001')->first()?->id,
                'agent_id' => Agent::where('matricule', 'AGT-003')->first()?->id,
                'gestionnaire_stock_id' => $gestionnaire?->id,
                'technicien_id' => $technicien?->id,
                'date_declaration' => now()->subDays(3),
                'description' => 'Le téléphone ne charge plus et s\'éteint aléatoirement',
                'niveau_gravite' => 'critique',
                'photos' => json_encode(['panne_003_1.jpg']),
                'diagnostic_technicien' => 'Connecteur de charge endommagé + batterie défectueuse',
                'action_realisee' => null,
                'cout_reparation' => 120.00,
                'statut' => 'en_maintenance',
                'date_resolution' => null,
                'solution' => null,
                'decision_finale' => 'en_attente',
            ],
        ];

        foreach ($pannes as $panneData) {
            if ($panneData['equipement_id'] && $panneData['agent_id']) {
                Panne::create($panneData);
            }
        }

        echo "✅ Pannes de test créées avec succès !\n";
    }
}