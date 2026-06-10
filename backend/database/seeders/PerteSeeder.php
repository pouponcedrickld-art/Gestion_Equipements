<?php

// database/seeders/PerteSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Perte;
use App\Models\Equipement;
use App\Models\Agent;
use App\Models\User;

class PerteSeeder extends Seeder
{
    public function run(): void
    {
        $equipement = Equipement::where('reference', 'PDA-001')->first();
        $agent = Agent::where('matricule', 'AGT-001')->first();
        $gestionnaire = User::role('gestionnaire_stock')->first();

        if (!$equipement || !$agent) {
            echo "⚠️ Données manquantes pour les pertes.\n";
            return;
        }

        $pertes = [
            [
                'equipement_id' => $equipement->id,
                'agent_id' => $agent->id,
                'type' => 'perte',
                'date_declaration' => now()->subDays(20),
                'description' => 'PDA perdu lors d\'une mission sur le terrain - zone rurale non couverte',
                'statut' => 'validee',
                'valide_par' => $gestionnaire?->id,
                'date_validation' => now()->subDays(15),
            ],
            [
                'equipement_id' => Equipement::where('reference', 'SCN-001')->first()?->id,
                'agent_id' => Agent::where('matricule', 'AGT-002')->first()?->id,
                'type' => 'casse',
                'date_declaration' => now()->subDays(5),
                'description' => 'Scanner tombé et écran brisé lors du transport',
                'statut' => 'declaree',
                'valide_par' => null,
                'date_validation' => null,
            ],
        ];

        foreach ($pertes as $perteData) {
            if ($perteData['equipement_id'] && $perteData['agent_id']) {
                Perte::create($perteData);
            }
        }

        echo "✅ Pertes de test créées avec succès !\n";
    }
}