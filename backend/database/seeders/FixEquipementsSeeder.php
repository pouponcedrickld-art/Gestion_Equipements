<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipement;
use App\Models\Categorie;

class FixEquipementsSeeder extends Seeder
{
    public function run(): void
    {
        // S'assurer qu'on a des catégories
        $cat = Categorie::first() ?: Categorie::create(['nom' => 'Informatique', 'description' => 'Matériel informatique']);

        Equipement::updateOrCreate(
            ['reference' => 'REF-FIX-001'],
            [
                'nom' => 'Ordinateur de Bureau',
                'numero_serie' => 'SN-FIX-001',
                'code_inventaire' => 'INV-FIX-001',
                'marque' => 'HP',
                'modele' => 'EliteDesk',
                'categorie_id' => $cat->id,
                'etat' => 'neuf',
                'statut_global' => 'en_stock_general',
            ]
        );

        Equipement::updateOrCreate(
            ['reference' => 'REF-FIX-002'],
            [
                'nom' => 'Écran 24 pouces',
                'numero_serie' => 'SN-FIX-002',
                'code_inventaire' => 'INV-FIX-002',
                'marque' => 'Dell',
                'modele' => 'P2419H',
                'categorie_id' => $cat->id,
                'etat' => 'neuf',
                'statut_global' => 'en_stock_general',
            ]
        );
    }
}
