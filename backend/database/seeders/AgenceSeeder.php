<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agence;

class AgenceSeeder extends Seeder
{
    public function run(): void
    {
        // Agence Générale (Siège)
        $agenceGenerale = Agence::updateOrCreate(
            ['nom' => 'Siège Social'],
            [
                'type' => 'generale',
                'adresse' => '123 Avenue Principale',
                'ville' => 'Lomé',
                'statut' => 'active',
            ]
        );

        // Sous-Agence Lomé
        Agence::updateOrCreate(
            ['nom' => 'Agence Lomé-Centre'],
            [
                'type' => 'sous_agence',
                'parent_id' => $agenceGenerale->id,
                'adresse' => '45 Rue du Commerce',
                'ville' => 'Lomé',
                'statut' => 'active',
            ]
        );

        // Sous-Agence Kara
        Agence::updateOrCreate(
            ['nom' => 'Agence Kara'],
            [
                'type' => 'sous_agence',
                'parent_id' => $agenceGenerale->id,
                'adresse' => '78 Boulevard de la République',
                'ville' => 'Kara',
                'statut' => 'active',
            ]
        );

        // Sous-Agence Sokodé
        Agence::updateOrCreate(
            ['nom' => 'Agence Sokodé'],
            [
                'type' => 'sous_agence',
                'parent_id' => $agenceGenerale->id,
                'adresse' => '12 Avenue des Palmiers',
                'ville' => 'Sokodé',
                'statut' => 'active',
            ]
        );

        echo "✅ Agences créées ou mises à jour avec succès !\n";
        echo "🏢 Siège Social + 3 sous-agences (Lomé, Kara, Sokodé)\n";
    }
}
