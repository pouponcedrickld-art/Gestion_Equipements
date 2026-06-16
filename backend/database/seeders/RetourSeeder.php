<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transfert;
use App\Models\Equipement;
use App\Models\Agence;
use App\Models\User;

class RetourSeeder extends Seeder
{
    public function run(): void
    {
        // Les retours sont aussi des transferts — déjà truncatés par TransfertSeeder
        // On ne truncate pas ici pour éviter de supprimer les transferts créés avant

        $agenceGenerale = Agence::where('type', 'generale')->first();
        $sousAgences = Agence::where('type', 'sous_agence')->get();
        $gestionnaireGeneral = User::role('gestionnaire_stock_general')->first();
        $chefAgence = User::role('chef_agence')->first();

        if (!$agenceGenerale || $sousAgences->isEmpty()) {
            echo "⚠️ Agences manquantes. Exécutez d'abord AgenceSeeder.\n";
            return;
        }

        $retours = [
            [
                'ref' => 'SPH-001',
                'source' => 'Lomé',
                'type' => 'retour_generale',
                'statut' => 'recu',
                'date_demande' => now()->subDays(6),
                'date_expedition' => now()->subDays(5),
                'date_reception' => now()->subDays(4),
                'valide_par' => $gestionnaireGeneral->id ?? 1,
                'obs' => 'Retour de smartphone S62 Pro - Fin d\'affectation',
            ],
            [
                'ref' => 'PDA-002',
                'source' => 'Lomé',
                'type' => 'retour_generale',
                'statut' => 'expedie',
                'date_demande' => now()->subDays(2),
                'date_expedition' => now()->subHours(12),
                'date_reception' => null,
                'valide_par' => $gestionnaireGeneral->id ?? 1,
                'obs' => 'Retour PDA MC3300 #2 pour révision technique',
            ],
            [
                'ref' => 'PDA-001',
                'source' => 'Kara',
                'type' => 'retour_generale',
                'statut' => 'demande',
                'date_demande' => now()->subHours(6),
                'date_expedition' => null,
                'date_reception' => null,
                'valide_par' => null,
                'obs' => 'Demande de retour PDA pour mise à jour logicielle',
            ],
            [
                'ref' => 'TAB-001',
                'source' => 'Kara',
                'type' => 'retour_generale',
                'statut' => 'recu',
                'date_demande' => now()->subDays(15),
                'date_expedition' => now()->subDays(14),
                'date_reception' => now()->subDays(12),
                'valide_par' => $gestionnaireGeneral->id ?? 1,
                'obs' => 'Retour tablette Zebra ET51 pour réparation - Hors garantie',
            ],
            [
                'ref' => 'PDA-001',
                'source' => 'Sokodé',
                'type' => 'retour_generale',
                'statut' => 'refuse',
                'date_demande' => now()->subDays(3),
                'date_expedition' => null,
                'date_reception' => null,
                'valide_par' => $gestionnaireGeneral->id ?? 1,
                'obs' => 'Retour refusé - Équipement nécessaire sur site',
            ],
        ];

        $count = 0;
        foreach ($retours as $item) {
            $equipement = Equipement::where('reference', $item['ref'])->first();
            if (!$equipement) continue;

            $sourceAgence = $sousAgences->where('ville', $item['source'])->first();
            if (!$sourceAgence) $sourceAgence = $sousAgences->first();

            Transfert::create([
                'equipement_id' => $equipement->id,
                'agence_source_id' => $sourceAgence->id,
                'agence_destination_id' => $agenceGenerale->id,
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

        echo "✅ {$count} retours (vers siège) créés avec différents statuts !\n";
    }
}
