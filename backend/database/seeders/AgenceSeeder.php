<?php

// database/seeders/AgenceSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agence;

class AgenceSeeder extends Seeder
{
    public function run(): void
    {
        $agenceGenerale = Agence::updateOrCreate(
            ['nom' => 'Siège Social'],
            [
                'type' => 'generale',
                'adresse' => '123 Avenue Principale',
                'ville' => 'Lomé',
                'code_postal' => 'BP 1234',
                'telephone' => '+228 22 21 00 01',
                'email' => 'contact@gestpark.tg',
                'statut' => 'active',
            ]
        );

        Agence::updateOrCreate(
            ['nom' => 'Agence Lomé-Centre'],
            [
                'type' => 'sous_agence',
                'parent_id' => $agenceGenerale->id,
                'adresse' => '45 Rue du Commerce',
                'ville' => 'Lomé',
                'code_postal' => 'BP 5678',
                'telephone' => '+228 22 25 10 20',
                'email' => 'lome@gestpark.tg',
                'statut' => 'active',
            ]
        );

        Agence::updateOrCreate(
            ['nom' => 'Agence Kara'],
            [
                'type' => 'sous_agence',
                'parent_id' => $agenceGenerale->id,
                'adresse' => '78 Boulevard de la République',
                'ville' => 'Kara',
                'code_postal' => 'BP 9012',
                'telephone' => '+228 26 60 30 40',
                'email' => 'kara@gestpark.tg',
                'statut' => 'active',
            ]
        );

        Agence::updateOrCreate(
            ['nom' => 'Agence Sokodé'],
            [
                'type' => 'sous_agence',
                'parent_id' => $agenceGenerale->id,
                'adresse' => '12 Avenue des Palmiers',
                'ville' => 'Sokodé',
                'code_postal' => 'BP 3456',
                'telephone' => '+228 25 50 20 10',
                'email' => 'sokode@gestpark.tg',
                'statut' => 'active',
            ]
        );

        echo "✅ Agences créées ou mises à jour avec succès !\n";
        echo "🏢 Siège Social + 3 sous-agences (Lomé, Kara, Sokodé)\n";
    }
}