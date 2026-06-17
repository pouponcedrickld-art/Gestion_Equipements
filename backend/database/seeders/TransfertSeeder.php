<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transfert;
use App\Models\Equipement;
use App\Models\Agence;
use App\Models\User;

class TransfertSeeder extends Seeder
{
    public function run(): void
    {
        Transfert::truncate();

        $agenceGenerale = Agence::where('type', 'generale')->first();
        $sousAgences = Agence::where('type', 'sous_agence')->get();
        $gestionnaireGeneral = User::role('gestionnaire_stock_general')->first();
        $chefAgence = User::role('chef_agence')->first();

        if (!$agenceGenerale || $sousAgences->isEmpty()) {
            echo "⚠️ Agences manquantes. Exécutez d'abord AgenceSeeder.\n";
            return;
        }

        $livraisons = [
            [
                'ref' => 'PDA-001',
                'destination' => 'Sokodé',
                'type' => 'livraison_generale',
                'statut' => 'expedie',
                'date_demande' => now()->subDays(3),
                'date_expedition' => now()->subDays(1),
                'date_reception' => null,
                'valide_par' => $gestionnaireGeneral->id ?? 1,
                'obs' => 'PDA Zebra MC3300 pour le responsable IT de Sokodé',
            ],
            [
                'ref' => 'SPH-001',
                'destination' => 'Lomé',
                'type' => 'livraison_generale',
                'statut' => 'demande',
                'date_demande' => now()->subDays(1),
                'date_expedition' => null,
                'date_reception' => null,
                'valide_par' => null,
                'obs' => 'Smartphone Cat S62 Pro pour renforcer l\'agence de Lomé',
            ],
            [
                'ref' => 'PDA-002',
                'destination' => 'Lomé',
                'type' => 'livraison_generale',
                'statut' => 'recu',
                'date_demande' => now()->subDays(10),
                'date_expedition' => now()->subDays(8),
                'date_reception' => now()->subDays(7),
                'valide_par' => $gestionnaireGeneral->id ?? 1,
                'obs' => 'PDA Zebra MC3300 #2 livré avec succès à Lomé',
            ],
            [
                'ref' => 'PDA-001',
                'destination' => 'Kara',
                'type' => 'transfert_interne',
                'statut' => 'recu',
                'date_demande' => now()->subDays(5),
                'date_expedition' => now()->subDays(4),
                'date_reception' => now()->subDays(3),
                'valide_par' => $gestionnaireGeneral->id ?? 1,
                'obs' => 'Transfert interne de Sokodé vers Kara pour maintenance',
            ],
            [
                'ref' => 'PDA-001',
                'destination' => 'Kara',
                'type' => 'livraison_generale',
                'statut' => 'refuse',
                'date_demande' => now()->subDays(2),
                'date_expedition' => null,
                'date_reception' => null,
                'valide_par' => $gestionnaireGeneral->id ?? 1,
                'obs' => 'Équipement non disponible - Stock insuffisant',
            ],
        ];

        $count = 0;
        foreach ($livraisons as $item) {
            $equipement = Equipement::where('reference', $item['ref'])->first();
            if (!$equipement) continue;

            $sourceId = $agenceGenerale->id;
            $destAgence = $sousAgences->where('ville', $item['destination'])->first();
            if (!$destAgence) $destAgence = $sousAgences->first();

            Transfert::create([
                'equipement_id' => $equipement->id,
                'agence_source_id' => $sourceId,
                'agence_destination_id' => $destAgence->id,
                'type_transfert' => $item['type'],
                'statut' => $item['statut'],
                'date_demande' => $item['date_demande'],
                'date_expedition' => $item['date_expedition'],
                'date_reception' => $item['date_reception'],
                'demande_par_id' => $chefAgence->id ?? 1,
                'valide_par_id' => $item['valide_par'],
                'quantite' => 1,
                'observations' => $item['obs'],
            ]);
            $count++;
        }

        echo "✅ {$count} transferts (livraisons + interne) créés avec différents statuts !\n";
    }
}
