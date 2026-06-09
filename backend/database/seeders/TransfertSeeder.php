<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transfert;
use App\Models\Agence;
use App\Models\User;

class TransfertSeeder extends Seeder
{
    public function run(): void
    {
        $agences = Agence::limit(3)->get();
        $users = User::limit(10)->get();

        if ($agences->count() < 2 || $users->isEmpty()) {
            return;
        }

        $source = $agences[0];
        $destination = $agences[1];

        $demandePar = $users->first();
        $validePar = $users->last();

        // Si votre modèle attend d’autres colonnes, adaptez ensuite.
        Transfert::create([
            'agence_source_id' => $source->id,
            'agence_destination_id' => $destination->id,
            'demande_par_id' => $demandePar->id,
            'valide_par_id' => $validePar->id,
            'statut' => 'demande',
            'motif' => 'Transfert de présentation',
            'date_demande' => now(),
        ]);

        echo "✅ Transfert fictif créé\n";
    }
}

