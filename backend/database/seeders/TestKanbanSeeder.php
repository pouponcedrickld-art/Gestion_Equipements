<?php

// database/seeders/TestKanbanSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DemandeMateriel;
use App\Models\Equipement;
use App\Models\User;
use App\Models\Agence;

class TestKanbanSeeder extends Seeder
{
    public function run(): void
    {
        $equip = Equipement::first();
        $chef = User::where('email', 'cheflome@gestpark.local')->first();
        $agence = Agence::where('nom', 'Agence Lomé-Centre')->first();

        if ($equip && $chef && $agence) {
            DemandeMateriel::create([
                'agence_id' => $agence->id,
                'chef_agence_id' => $chef->id,
                'equipement_id' => $equip->id,
                'quantite' => 2,
                'urgence' => 'Haute',
                'motif' => 'Besoin urgent pour nouveau projet de collecte de données dans la région des plateaux',
                'date_souhaitee' => now()->addDays(5),
                'statut' => 'approuve',
            ]);
        }

        echo "✅ Données Kanban de test créées !\n";
    }
}