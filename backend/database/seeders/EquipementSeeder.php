<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipement;
use App\Models\Categorie;
use App\Models\Agence;
use App\Models\Mouvement;

class EquipementSeeder extends Seeder
{
    public function run(): void
    {
        $agenceGenerale = Agence::where('type', 'generale')->first();
        $sousAgences = Agence::where('type', 'sous_agence')->get();
        
        $categories = Categorie::all();

        $equipements = [
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
                'localisation' => 'Stock Central',
                'agence_proprietaire_id' => $agenceGenerale->id,
                'agence_actuelle_id' => $agenceGenerale->id,
                'qr_code' => 'QR_PDA_001',
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
                'localisation' => 'Lomé - Stock',
                'agence_proprietaire_id' => $agenceGenerale->id,
                'agence_actuelle_id' => $sousAgences->first()->id,
                'qr_code' => 'QR_PDA_002',
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
                'localisation' => 'Agent - Jean Dupont',
                'agence_proprietaire_id' => $agenceGenerale->id,
                'agence_actuelle_id' => $sousAgences->first()->id,
                'qr_code' => 'QR_SPH_001',
            ],
            [
                'nom' => 'Scanner Zebra DS4608',
                'reference' => 'SCN-001',
                'numero_serie' => 'DS4608-001',
                'code_inventaire' => 'INV-SCN-001',
                'marque' => 'Zebra',
                'modele' => 'DS4608',
                'categorie_id' => $categories->where('nom', 'Scanner code-barres')->first()->id,
                'fournisseur' => 'Zebra Technologies',
                'date_acquisition' => '2024-03-05',
                'prix_achat' => 250.00,
                'garantie_date_fin' => '2026-03-05',
                'etat' => 'neuf',
                'statut_global' => 'en_stock_general',
                'localisation' => 'Stock Central - Neuf',
                'agence_proprietaire_id' => $agenceGenerale->id,
                'agence_actuelle_id' => $agenceGenerale->id,
                'qr_code' => 'QR_SCN_001',
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
                'localisation' => 'Kara - Maintenance',
                'agence_proprietaire_id' => $agenceGenerale->id,
                'agence_actuelle_id' => $sousAgences->where('ville', 'Kara')->first()->id,
                'qr_code' => 'QR_TAB_001',
            ],
            [
                'nom' => 'PC Portable Dell Latitude 5520',
                'reference' => 'PC-001',
                'numero_serie' => 'LAPTOP-001',
                'code_inventaire' => 'INV-PC-001',
                'marque' => 'Dell',
                'modele' => 'Latitude 5520',
                'categorie_id' => $categories->where('nom', 'Ordinateur portable')->first()->id,
                'fournisseur' => 'Dell Technologies',
                'date_acquisition' => '2024-02-01',
                'prix_achat' => 1500.00,
                'garantie_date_fin' => '2027-02-01',
                'etat' => 'en_service',
                'statut_global' => 'en_transit',
                'localisation' => 'En transfert vers Sokodé',
                'agence_proprietaire_id' => $agenceGenerale->id,
                'agence_actuelle_id' => $agenceGenerale->id,
                'qr_code' => 'QR_PC_001',
            ],
        ];

        foreach ($equipements as $equipementData) {
            $equipement = Equipement::create($equipementData);
            
            Mouvement::create([
                'equipement_id' => $equipement->id,
                'type_mouvement' => 'creation',
                'agent_id' => null,
                'user_id' => 1,
                'date_mouvement' => $equipement->date_acquisition,
                'ancienne_valeur' => null,
                'nouvelle_valeur' => json_encode([
                    'etat' => $equipement->etat,
                    'statut_global' => $equipement->statut_global,
                    'agence_actuelle' => $equipement->agence_actuelle_id,
                ]),
                'description' => 'Acquisition initiale - ' . $equipement->marque . ' ' . $equipement->modele,
            ]);
        }

        echo "✅ " . count($equipements) . " équipements de test créés avec mouvements initiaux !\n";
    }
}
