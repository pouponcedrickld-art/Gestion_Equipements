<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipementSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('equipements')->insert([
            [
                'nom' => 'Ordinateur Portable',
                'reference' => 'REF-LAP-001',
                'numero_serie' => 'SN-123456789',
                'code_inventaire' => 'INV-2026-001',
                'marque' => 'Dell',
                'modele' => 'Latitude 5440',
                'categorie_id' => 5, // Assurez-vous que cette ID existe dans votre table categories
                'etat' => 'neuf',
                'created_at' => now(),
            ],
            [
                'nom' => 'Imprimante Réseau',
                'reference' => 'REF-IMP-002',
                'numero_serie' => 'SN-987654321',
                'code_inventaire' => 'INV-2026-002',
                'marque' => 'HP',
                'modele' => 'LaserJet Pro',
                'categorie_id' => 6,
                'etat' => 'en_service',
                'created_at' => now(),
            ]
        ]);
    }
}