<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipement;
use App\Models\Categorie;
use App\Models\Agence;
use App\Models\Mouvement;
use Illuminate\Support\Str;

class EquipementSeeder extends Seeder
{
    public function run(): void
    {
        $agenceGenerale = Agence::where('type', 'generale')->first();
        $sousAgences = Agence::where('type', 'sous_agence')->get();
        $categories = Categorie::all();

        if ($categories->isEmpty()) {
            echo "⚠️ Aucune catégorie trouvée. Exécutez d'abord CategorieSeeder.\n";
            return;
        }

        $marques = ['Zebra', 'Honeywell', 'Datalogic', 'Dell', 'HP', 'Lenovo', 'Caterpillar', 'Samsung'];
        $etats = ['neuf', 'en_service', 'en_panne', 'en_maintenance', 'reforme', 'perdu'];
        $statuts_globaux = ['en_stock_general', 'en_stock_local', 'affecte', 'en_transit', 'en_panne', 'en_maintenance', 'reforme'];

        // 1. Création des équipements fixes (pour les relations dans les autres seeders)
        $fixedEquipements = [
            [
                'nom' => 'PDA Zebra MC3300',
                'reference' => 'PDA-001',
                'numero_serie' => 'MC3300-001',
                'imei' => '123456789012345',
                'code_inventaire' => 'INV-PDA-001',
                'marque' => 'Zebra',
                'modele' => 'MC3300',
                'categorie_id' => $categories->where('nom', 'PDA')->first()->id,
                'fournisseur' => 'Zebra Technologies',
                'date_acquisition' => '2024-01-15',
                'prix_achat' => 850.00,
                'garantie_date_fin' => '2026-01-15',
                'etat' => 'en_service',
                'statut_global' => 'en_stock_general',
                'agence_proprietaire_id' => $agenceGenerale->id,
                'agence_actuelle_id' => $agenceGenerale->id,
            ],
            [
                'nom' => 'PDA Zebra MC3300 #2',
                'reference' => 'PDA-002',
                'numero_serie' => 'MC3300-002',
                'imei' => '123456789012346',
                'code_inventaire' => 'INV-PDA-002',
                'marque' => 'Zebra',
                'modele' => 'MC3300',
                'categorie_id' => $categories->where('nom', 'PDA')->first()->id,
                'fournisseur' => 'Zebra Technologies',
                'date_acquisition' => '2024-01-15',
                'prix_achat' => 850.00,
                'garantie_date_fin' => '2026-01-15',
                'etat' => 'en_service',
                'statut_global' => 'en_stock_local',
                'agence_proprietaire_id' => $agenceGenerale->id,
                'agence_actuelle_id' => $sousAgences->first()->id,
            ],
            [
                'nom' => 'Smartphone Cat S62 Pro',
                'reference' => 'SPH-001',
                'numero_serie' => 'IP68-001',
                'imei' => '987654321098765',
                'code_inventaire' => 'INV-SPH-001',
                'marque' => 'Cat',
                'modele' => 'S62 Pro',
                'categorie_id' => $categories->where('nom', 'Smartphone')->first()->id,
                'fournisseur' => 'Caterpillar',
                'date_acquisition' => '2024-02-10',
                'prix_achat' => 650.00,
                'garantie_date_fin' => '2026-02-10',
                'etat' => 'en_service',
                'statut_global' => 'affecte',
                'agence_proprietaire_id' => $agenceGenerale->id,
                'agence_actuelle_id' => $sousAgences->first()->id,
            ],
            [
                'nom' => 'Tablette Zebra ET51',
                'reference' => 'TAB-001',
                'numero_serie' => 'ET51-001',
                'code_inventaire' => 'INV-TAB-001',
                'marque' => 'Zebra',
                'modele' => 'ET51',
                'categorie_id' => $categories->where('nom', 'Tablette')->first()->id,
                'fournisseur' => 'Zebra Technologies',
                'date_acquisition' => '2024-01-20',
                'prix_achat' => 1200.00,
                'garantie_date_fin' => '2026-01-20',
                'etat' => 'en_panne',
                'statut_global' => 'en_panne',
                'agence_proprietaire_id' => $agenceGenerale->id,
                'agence_actuelle_id' => $sousAgences->where('ville', 'Kara')->first()->id,
            ],
        ];

        foreach ($fixedEquipements as $data) {
            $data['qr_code'] = 'QR_' . $data['reference'];
            Equipement::updateOrCreate(['reference' => $data['reference']], $data);
        }

        // 2. Création massive d'équipements aléatoires (30 de plus)
        for ($i = 3; $i <= 33; $i++) {
            $marque = $marques[array_rand($marques)];
            $categorie = $categories->random();
            $etat = $etats[array_rand($etats)];
            
            // Logique de statut cohérente avec l'état
            if ($etat === 'en_panne') $statut_global = 'en_panne';
            elseif ($etat === 'en_maintenance') $statut_global = 'en_maintenance';
            elseif ($etat === 'reforme') $statut_global = 'reforme';
            elseif ($etat === 'perdu') $statut_global = 'reforme';
            else $statut_global = $statuts_globaux[array_rand(['en_stock_general', 'en_stock_local', 'affecte', 'en_transit'])];

            $agence = ($statut_global === 'en_stock_general') ? $agenceGenerale : $sousAgences->random();

            Equipement::create([
                'nom' => $categorie->nom . ' ' . $marque . ' ' . Str::random(4),
                'reference' => mb_strtoupper(mb_substr($categorie->nom, 0, 1)) . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'numero_serie' => strtoupper(Str::random(10)),
                'imei' => ($categorie->nom === 'Smartphone' || $categorie->nom === 'PDA') ? '35' . rand(1000000000000, 9999999999999) : null,
                'code_inventaire' => 'INV-' . strtoupper(Str::random(8)),
                'marque' => $marque,
                'modele' => 'Mod-' . rand(100, 999),
                'categorie_id' => $categorie->id,
                'fournisseur' => 'Fournisseur ' . rand(1, 5),
                'date_acquisition' => now()->subMonths(rand(1, 24))->format('Y-m-d'),
                'prix_achat' => rand(200, 2000),
                'garantie_date_fin' => now()->addMonths(rand(-6, 24))->format('Y-m-d'),
                'etat' => $etat,
                'statut_global' => $statut_global,
                'localisation' => 'Localisation ' . rand(1, 10),
                'agence_proprietaire_id' => $agenceGenerale->id,
                'agence_actuelle_id' => $agence->id,
                'qr_code' => 'QR_' . Str::random(10),
            ]);
        }

        echo "✅ " . (count($fixedEquipements) + 31) . " équipements créés pour peupler le dashboard !\n";
    }
}
