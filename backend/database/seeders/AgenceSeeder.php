<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agence;

class AgenceSeeder extends Seeder
{
    public function run(): void
    {
        // Agence Générale (Siège)
        $agenceGenerale = Agence::create([
            'type' => 'generale',
            'nom' => 'Siège Social',
            'adresse' => '123 Avenue Principale',
            'ville' => 'Lomé',
            'statut' => 'active',
        ]);

        // Sous-Agence Lomé
        Agence::create([
            'type' => 'sous_agence',
            'parent_id' => $agenceGenerale->id,
            'nom' => 'Agence Lomé-Centre',
            'adresse' => '45 Rue du Commerce',
            'ville' => 'Lomé',
            'statut' => 'active',
        ]);

        // Sous-Agence Kara
        Agence::create([
            'type' => 'sous_agence',
            'parent_id' => $agenceGenerale->id,
            'nom' => 'Agence Kara',
            'adresse' => '78 Boulevard de la République',
            'ville' => 'Kara',
            'statut' => 'active',
        ]);

        // Sous-Agence Sokodé
        Agence::create([
            'type' => 'sous_agence',
            'parent_id' => $agenceGenerale->id,
            'nom' => 'Agence Sokodé',
            'adresse' => '12 Avenue des Palmiers',
            'ville' => 'Sokodé',
            'statut' => 'active',
        ]);

        echo "✅ Agences créées avec succès !\n";
        echo "🏢 Siège Social + 3 sous-agences (Lomé, Kara, Sokodé)\n";
    }
}
