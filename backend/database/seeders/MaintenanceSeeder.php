<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Maintenance;
use App\Models\Equipement;
use App\Models\User;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        $equipements = Equipement::all();
        $techniciens = User::role('technicien_maintenance')->get();
        $gestionnaires = User::role(['gestionnaire_stock_general', 'gestionnaire_stock'])->get();

        if ($equipements->isEmpty()) {
            echo "⚠️ Aucun équipement trouvé.\n";
            return;
        }

        $types = ['préventif', 'correctif'];
        $statuts = ['planifiee', 'en_cours', 'terminee', 'annulee'];

        // Créer 10 maintenances
        for ($i = 1; $i <= 10; $i++) {
            $equipement = $equipements->random();
            $statut = $statuts[array_rand($statuts)];
            $type = $types[array_rand($types)];
            
            $date_prevue = now()->addDays(rand(-30, 30));
            $date_debut = in_array($statut, ['en_cours', 'terminee']) ? (clone $date_prevue)->setTime(9, 0) : null;
            $date_fin = ($statut === 'terminee') ? (clone $date_debut)->addHours(rand(1, 4)) : null;

            Maintenance::create([
                'equipement_id' => $equipement->id,
                'type_maintenance' => $type,
                'date_prevue' => $date_prevue,
                'responsable' => $gestionnaires->random()->name,
                'technicien_id' => $techniciens->random()->id,
                'diagnostic' => 'Vérification de maintenance ' . $i,
                'cout' => $statut === 'terminee' ? rand(0, 300) : 0,
                'date_debut' => $date_debut,
                'date_fin' => $date_fin,
                'observations' => 'Observation de maintenance ' . $i,
                'statut' => $statut,
            ]);

            if ($statut === 'en_cours') {
                $equipement->update(['etat' => 'en_maintenance', 'statut_global' => 'en_maintenance']);
            }
        }

        echo "✅ 10 maintenances créées avec succès !\n";
    }
}
