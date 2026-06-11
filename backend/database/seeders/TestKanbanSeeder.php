<?php

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
        $chef = User::where('id', 3)->first(); // Chef Agence Lomé
        $agence = Agence::where('id', 2)->first(); // Agence Lomé

        if ($equip && $chef && $agence) {
            DemandeMateriel::create([
                'agence_id' => $agence->id,
                'chef_agence_id' => $chef->id,
                'equipement_id' => $equip->id,
                'quantite' => 2,
                'urgence' => 'Haute',
                'motif' => 'Besoin urgent pour nouveau projet',
                'date_souhaitee' => now()->addDays(5),
                'statut' => 'approuvé',
            ]);
        }
    }
}
