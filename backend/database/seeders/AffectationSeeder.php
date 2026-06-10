<?php

// database/seeders/AffectationSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Affectation;
use App\Models\Agent;
use App\Models\Equipement;
use App\Models\User;

class AffectationSeeder extends Seeder
{
    public function run(): void
    {
        $agent = Agent::where('matricule', 'AGT-001')->first();
        $equipement = Equipement::where('reference', 'SPH-001')->first();
        $gestionnaire = User::role('gestionnaire_stock')->first();

        if (!$agent || !$equipement || !$gestionnaire) {
            echo "⚠️ Données manquantes pour les affectations.\n";
            return;
        }

        $affectations = [
            [
                'agent_id' => $agent->id,
                'equipement_id' => $equipement->id,
                'date_affectation' => now()->subDays(30),
                'date_retour_prevu' => now()->addDays(60),
                'date_retour_effectif' => null,
                'affecte_par' => $gestionnaire->id,
                'etat_retour' => null,
                'observations' => 'Affectation pour mission de collecte de données',
                'pv_remise_path' => null,
                'statut' => 'active',
            ],
            [
                'agent_id' => Agent::where('matricule', 'AGT-002')->first()?->id,
                'equipement_id' => Equipement::where('reference', 'PDA-002')->first()?->id,
                'date_affectation' => now()->subDays(15),
                'date_retour_prevu' => now()->addDays(45),
                'date_retour_effectif' => null,
                'affecte_par' => $gestionnaire->id,
                'etat_retour' => null,
                'observations' => 'Affectation temporaire pour formation',
                'pv_remise_path' => null,
                'statut' => 'active',
            ],
            [
                'agent_id' => Agent::where('matricule', 'AGT-003')->first()?->id,
                'equipement_id' => Equipement::where('reference', 'TAB-001')->first()?->id,
                'date_affectation' => now()->subDays(60),
                'date_retour_prevu' => now()->subDays(30),
                'date_retour_effectif' => now()->subDays(25),
                'affecte_par' => $gestionnaire->id,
                'etat_retour' => 'bon',
                'observations' => 'Retour effectué sans problème',
                'pv_remise_path' => 'pv/pv_retour_001.pdf',
                'statut' => 'retournee',
            ],
        ];

        foreach ($affectations as $affectationData) {
            if ($affectationData['agent_id'] && $affectationData['equipement_id']) {
                Affectation::create($affectationData);
            }
        }

        echo "✅ Affectations de test créées avec succès !\n";
    }
}