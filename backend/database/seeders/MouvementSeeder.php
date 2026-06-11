<?php

// database/seeders/MouvementSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mouvement;
use App\Models\Equipement;
use App\Models\Agent;
use App\Models\User;

class MouvementSeeder extends Seeder
{
    public function run(): void
    {
        $equipements = Equipement::all();
        $agent = Agent::where('matricule', 'AGT-001')->first();
        $gestionnaire = User::role('gestionnaire_stock_general')->first();

        if ($equipements->isEmpty()) {
            echo "⚠️ Aucun équipement trouvé.\n";
            return;
        }

        $mouvements = [
            [
                'type_mouvement' => 'affectation',
                'equipement_id' => $equipements->where('reference', 'SPH-001')->first()->id,
                'agent_id' => $agent?->id,
                'user_id' => $gestionnaire?->id ?? 1,
                'date_mouvement' => now()->subDays(30),
                'ancienne_valeur' => json_encode(['statut_global' => 'en_stock_local', 'agent_id' => null]),
                'nouvelle_valeur' => json_encode(['statut_global' => 'affecte', 'agent_id' => $agent?->id]),
                'description' => 'Affectation du smartphone à l\'agent Jean Dupont',
            ],
            [
                'type_mouvement' => 'transfert',
                'equipement_id' => $equipements->where('reference', 'PC-001')->first()->id,
                'agent_id' => null,
                'user_id' => $gestionnaire?->id ?? 1,
                'date_mouvement' => now()->subDays(3),
                'ancienne_valeur' => json_encode(['agence_actuelle_id' => 1, 'statut_global' => 'en_stock_general']),
                'nouvelle_valeur' => json_encode(['agence_actuelle_id' => 4, 'statut_global' => 'en_transit']),
                'description' => 'Transfert vers l\'agence de Sokodé',
            ],
            [
                'type_mouvement' => 'changement_etat',
                'equipement_id' => $equipements->where('reference', 'TAB-001')->first()->id,
                'agent_id' => null,
                'user_id' => $gestionnaire?->id ?? 1,
                'date_mouvement' => now()->subDays(10),
                'ancienne_valeur' => json_encode(['etat' => 'en_service', 'statut_global' => 'affecte']),
                'nouvelle_valeur' => json_encode(['etat' => 'en_panne', 'statut_global' => 'en_panne']),
                'description' => 'Déclaration de panne - écran tactile inopérant',
            ],
        ];

        foreach ($mouvements as $mouvementData) {
            if ($mouvementData['equipement_id']) {
                Mouvement::create($mouvementData);
            }
        }

        echo "✅ Mouvements de test créés avec succès !\n";
    }
}