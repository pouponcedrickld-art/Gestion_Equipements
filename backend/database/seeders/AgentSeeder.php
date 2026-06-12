<?php

// database/seeders/AgentSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agent;
use App\Models\User;
use App\Models\Agence;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        $agents = [
            [
                'matricule' => 'AGT-001',
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'telephone' => '+228 90 12 34 56',
                'email' => 'jean.dupont@gestpark.tg',
                'poste' => 'Agent de collecte',
                'statut' => 'actif',
                'user_id' => User::where('email', 'agent@gestpark.local')->first()?->id,
            ],
            [
                'matricule' => 'AGT-002',
                'nom' => 'Koffi',
                'prenom' => 'Marie',
                'telephone' => '+228 90 23 45 67',
                'email' => 'marie.koffi@gestpark.tg',
                'poste' => 'Agent de saisie',
                'statut' => 'actif',
                'user_id' => null,
            ],
            [
                'matricule' => 'AGT-003',
                'nom' => 'Amouzou',
                'prenom' => 'Kossi',
                'telephone' => '+228 90 34 56 78',
                'email' => 'kossi.amouzou@gestpark.tg',
                'poste' => 'Technicien terrain',
                'statut' => 'actif',
                'user_id' => User::where('email', 'technicien@gestpark.local')->first()?->id,
            ],
            [
                'matricule' => 'AGT-004',
                'nom' => 'Bodjona',
                'prenom' => 'Afi',
                'telephone' => '+228 90 45 67 89',
                'email' => 'afi.bodjona@gestpark.tg',
                'poste' => 'Agent de collecte',
                'statut' => 'inactif',
                'user_id' => null,
            ],
            [
                'matricule' => 'AGT-005',
                'nom' => 'Tchala',
                'prenom' => 'Kodjo',
                'telephone' => '+228 90 56 78 90',
                'email' => 'kodjo.tchala@gestpark.tg',
                'poste' => 'Superviseur terrain',
                'statut' => 'actif',
                'user_id' => null,
            ],
        ];

        foreach ($agents as $agentData) {
            Agent::updateOrCreate(['matricule' => $agentData['matricule']], $agentData);
        }

        echo "✅ " . count($agents) . " agents créés avec succès !\n";
    }
}