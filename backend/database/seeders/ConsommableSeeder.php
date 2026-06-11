<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Consommable;
use App\Models\Equipement;

class ConsommableSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer quelques équipements pour associer des consommables
        $equipements = Equipement::all();
        
        if ($equipements->isEmpty()) {
            echo "⚠️ Aucun équipement trouvé. Exécutez d'abord EquipementSeeder.\n";
            return;
        }

        $consommables = [
            // Consommables pour PDA
            [
                'nom' => 'Batterie MC3300 Standard',
                'type' => 'batterie',
                'equipement_id' => $equipements->where('reference', 'PDA-001')->first()->id ?? $equipements->first()->id,
                'quantite' => 2,
            ],
            [
                'nom' => 'Batterie MC3300 Extended',
                'type' => 'batterie',
                'equipement_id' => $equipements->where('reference', 'PDA-002')->first()->id ?? $equipements->first()->id,
                'quantite' => 1,
            ],
            [
                'nom' => 'Chargeur MC3300 4 slots',
                'type' => 'chargeur',
                'equipement_id' => $equipements->where('reference', 'PDA-001')->first()->id ?? $equipements->first()->id,
                'quantite' => 1,
            ],
            [
                'nom' => 'Étui holster MC3300',
                'type' => 'protection',
                'equipement_id' => $equipements->where('reference', 'PDA-001')->first()->id ?? $equipements->first()->id,
                'quantite' => 1,
            ],
            // Consommables pour Smartphone
            [
                'nom' => 'Batterie Cat S62 Pro',
                'type' => 'batterie',
                'equipement_id' => $equipements->where('reference', 'SPH-001')->first()->id ?? $equipements->skip(1)->first()->id,
                'quantite' => 1,
            ],
            [
                'nom' => 'Chargeur USB-C rapide',
                'type' => 'chargeur',
                'equipement_id' => $equipements->where('reference', 'SPH-001')->first()->id ?? $equipements->skip(1)->first()->id,
                'quantite' => 1,
            ],
            [
                'nom' => 'Protection écran Cat S62',
                'type' => 'protection',
                'equipement_id' => $equipements->where('reference', 'SPH-001')->first()->id ?? $equipements->skip(1)->first()->id,
                'quantite' => 2,
            ],
            // Consommables pour Tablette
            [
                'nom' => 'Batterie ET51 Extended',
                'type' => 'batterie',
                'equipement_id' => $equipements->where('reference', 'TAB-001')->first()->id ?? $equipements->skip(2)->first()->id,
                'quantite' => 1,
            ],
            [
                'nom' => 'Station de charge ET51',
                'type' => 'chargeur',
                'equipement_id' => $equipements->where('reference', 'TAB-001')->first()->id ?? $equipements->skip(2)->first()->id,
                'quantite' => 1,
            ],
            [
                'nom' => 'Housse de transport ET51',
                'type' => 'protection',
                'equipement_id' => $equipements->where('reference', 'TAB-001')->first()->id ?? $equipements->skip(2)->first()->id,
                'quantite' => 1,
            ],
            // Consommables génériques
            [
                'nom' => 'Câble USB-A vers micro-USB',
                'type' => 'câble',
                'equipement_id' => $equipements->first()->id,
                'quantite' => 5,
            ],
            [
                'nom' => 'Adaptateur secteur universel',
                'type' => 'chargeur',
                'equipement_id' => $equipements->first()->id,
                'quantite' => 3,
            ],
        ];

        foreach ($consommables as $consommableData) {
            Consommable::create($consommableData);
        }

        echo "✅ " . count($consommables) . " consommables de test créés !\n";
    }
}