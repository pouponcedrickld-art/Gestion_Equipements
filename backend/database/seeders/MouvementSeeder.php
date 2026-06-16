<?php

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
        $agents = Agent::all();
        $users = User::all();

        if ($equipements->isEmpty()) {
            echo "⚠️ Aucun équipement trouvé.\n";
            return;
        }

        $types = ['creation', 'affectation', 'retour', 'transfert', 'changement_etat', 'reforme'];

        // Créer 20 mouvements aléatoires pour peupler l'historique
        for ($i = 1; $i <= 20; $i++) {
            $equipement = $equipements->random();
            $type = $types[array_rand($types)];
            
            Mouvement::create([
                'type_mouvement' => $type,
                'equipement_id' => $equipement->id,
                'agent_id' => $type === 'affectation' || $type === 'retour' ? $agents->random()->id : null,
                'user_id' => $users->random()->id,
                'date_mouvement' => now()->subDays(rand(1, 90)),
                'description' => 'Mouvement automatique de test : ' . $type,
            ]);
        }

        echo "✅ 20 mouvements de test créés avec succès !\n";
    }
}
