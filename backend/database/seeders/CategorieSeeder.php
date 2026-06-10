<?php

// database/seeders/CategorieSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categorie;

class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nom' => 'PDA', 'description' => 'Personal Digital Assistant - Terminal de saisie portable'],
            ['nom' => 'Smartphone', 'description' => 'Téléphone professionnel robuste'],
            ['nom' => 'Tablette', 'description' => 'Tablette tactile professionnelle'],
            ['nom' => 'Scanner code-barres', 'description' => 'Lecteur de codes-barres portatif'],
            ['nom' => 'Ordinateur portable', 'description' => 'PC portable professionnel'],
            ['nom' => 'Imprimante mobile', 'description' => 'Imprimante thermique portable'],
            ['nom' => 'Batterie', 'description' => 'Batterie de rechange'],
            ['nom' => 'Chargeur', 'description' => 'Chargeur et accessoires d\'alimentation'],
            ['nom' => 'Étui / Protection', 'description' => 'Housses et protections d\'équipements'],
        ];

        foreach ($categories as $categorie) {
            Categorie::create($categorie);
        }

        echo "✅ " . count($categories) . " catégories d'équipements créées !\n";
    }
}