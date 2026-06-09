<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipement;
use App\Models\Categorie;
use App\Models\Agence;

class EquipementSeeder extends Seeder
{
    public function run(): void
    {
        $agences = Agence::limit(4)->get();
        $categories = Categorie::limit(10)->get();

        if ($agences->isEmpty() || $categories->isEmpty()) {
            return;
        }

        $faker = \Faker\Factory::create('fr_FR');

        $i = 0;
        foreach ($categories as $categorie) {
            $agence = $agences[$i % $agences->count()];

            for ($k = 0; $k < 3; $k++) {
                $serial = strtoupper($faker->bothify('SER#####'));
                $reference = strtoupper($faker->bothify('REF-?????'));

                Equipement::create([
                    'nom' => $faker->words(3, true),
                    'reference' => $reference,
                    'numero_serie' => $serial,
                    'imei' => $faker->randomNumber(14, true),
                    'code_inventaire' => strtoupper($faker->bothify('INV-########')),
                    'categorie_id' => $categorie->id,

                    'marque' => $faker->company,
                    'modele' => $faker->word,
                    'fournisseur' => $faker->company,

                    'date_acquisition' => $faker->dateTimeBetween('-3 years', 'now'),
                    'prix' => $faker->numberBetween(5000, 250000),
                    'garantie_mois' => $faker->numberBetween(6, 36),

                    'etat' => 'fonctionnel',

                    'agence_proprietaire_id' => $agence->id,
                    'agence_actuelle_id' => $agence->id,

                    // colonnes attendues (selon migration existante)
                    'statut_global' => 'en_stock_general',
                    'actif' => true,
                ]);
            }

            $i++;
        }

        echo "✅ Equipements fictifs créés\n";
    }
}

