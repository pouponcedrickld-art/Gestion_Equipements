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
        // Récupérer les données nécessaires
        $agenceGenerale = Agence::where('type', 'generale')->first();
        $sousAgences = Agence::where('type', 'sous_agence')->get();
        $equipements = Equipement::all();
        $gestionnaireGeneral = User::role('gestionnaire_stock_general')->first();
        $chefAgence = User::role('chef_agence')->first();
        
        if (!$agenceGenerale || $sousAgences->isEmpty() || $equipements->isEmpty()) {
            echo "⚠️ Données manquantes. Exécutez d'abord AgenceSeeder, EquipementSeeder et UserSeeder.\n";
            return;
        }

        $transferts = [
            // Transfert approuvé et expédié (prêt à être reçu)
            [
                'equipement_id' => $equipements->where('reference', 'PC-001')->first()->id ?? $equipements->first()->id,
                'agence_source_id' => $agenceGenerale->id,
                'agence_destination_id' => $sousAgences->where('ville', 'Sokodé')->first()->id ?? $sousAgences->first()->id,
                'type_transfert' => 'livraison_generale',
                'demande_par_id' => $chefAgence->id ?? 1,
                'valide_par_id' => $gestionnaireGeneral->id ?? 1,
                'date_demande' => now()->subDays(3),
                'date_expedition' => now()->subDays(1),
                'date_reception' => null,
                'statut' => 'expedie',
                'quantite' => 1,
                'observations' => 'Ordinateur portable pour le responsable IT de Sokodé',
            ],
            // Transfert en attente d'approbation
            [
                'equipement_id' => $equipements->where('reference', 'SCN-001')->first()->id ?? $equipements->skip(1)->first()->id,
                'agence_source_id' => $agenceGenerale->id,
                'agence_destination_id' => $sousAgences->where('ville', 'Lomé')->first()->id ?? $sousAgences->first()->id,
                'type_transfert' => 'livraison_generale',
                'demande_par_id' => $chefAgence->id ?? 1,
                'valide_par_id' => null,
                'date_demande' => now()->subDays(1),
                'date_expedition' => null,
                'date_reception' => null,
                'statut' => 'demande',
                'quantite' => 1,
                'observations' => 'Scanner pour renforcer l\'équipement de l\'agence de Lomé',
            ],
            // Transfert terminé (historique)
            [
                'equipement_id' => $equipements->where('reference', 'PDA-002')->first()->id ?? $equipements->skip(2)->first()->id,
                'agence_source_id' => $agenceGenerale->id,
                'agence_destination_id' => $sousAgences->where('ville', 'Lomé')->first()->id ?? $sousAgences->first()->id,
                'type_transfert' => 'livraison_generale',
                'demande_par_id' => $chefAgence->id ?? 1,
                'valide_par_id' => $gestionnaireGeneral->id ?? 1,
                'date_demande' => now()->subDays(10),
                'date_expedition' => now()->subDays(8),
                'date_reception' => now()->subDays(7),
                'statut' => 'recu',
                'quantite' => 1,
                'observations' => 'PDA pour agent terrain - Transfert effectué avec succès',
            ],
            // Transfert interne entre sous-agences
            [
                'equipement_id' => $equipements->where('reference', 'TAB-001')->first()->id ?? $equipements->skip(3)->first()->id,
                'agence_source_id' => $sousAgences->where('ville', 'Lomé')->first()->id ?? $sousAgences->first()->id,
                'agence_destination_id' => $sousAgences->where('ville', 'Kara')->first()->id ?? $sousAgences->skip(1)->first()->id,
                'type_transfert' => 'transfert_interne',
                'demande_par_id' => $chefAgence->id ?? 1,
                'valide_par_id' => $gestionnaireGeneral->id ?? 1,
                'date_demande' => now()->subDays(5),
                'date_expedition' => now()->subDays(4),
                'date_reception' => now()->subDays(3),
                'statut' => 'recu',
                'quantite' => 1,
                'observations' => 'Tablette transférée pour maintenance spécialisée à Kara',
            ],
            // Transfert refusé (exemple de workflow)
            [
                'equipement_id' => $equipements->first()->id,
                'agence_source_id' => $agenceGenerale->id,
                'agence_destination_id' => $sousAgences->last()->id,
                'type_transfert' => 'livraison_generale',
                'demande_par_id' => $chefAgence->id ?? 1,
                'valide_par_id' => $gestionnaireGeneral->id ?? 1,
                'date_demande' => now()->subDays(2),
                'date_expedition' => null,
                'date_reception' => null,
                'statut' => 'refuse',
                'quantite' => 1,
                'observations' => 'Équipement non disponible - Stock insuffisant',
            ],
        ];

        foreach ($transferts as $transfertData) {
            Transfert::create($transfertData);        }

        echo "✅ " . count($transferts) . " transferts de test créés avec différents statuts !\n";
    }
}