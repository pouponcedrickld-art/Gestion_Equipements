<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Panne;
use App\Models\Equipement;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Support\Str;

class PanneSeeder extends Seeder
{
    public function run(): void
    {
        $equipements = Equipement::all();
        $agents = Agent::all();
        $gestionnaires = User::role(['gestionnaire_stock', 'gestionnaire_stock_general'])->get();
        $techniciens = User::role('technicien_maintenance')->get();

        if ($equipements->isEmpty() || $agents->isEmpty()) {
            echo "⚠️ Données manquantes pour les pannes.\n";
            return;
        }

        $descriptions = [
            'L\'écran ne s\'allume plus',
            'Batterie qui gonfle',
            'Problème de connexion Wi-Fi',
            'Scanner de codes-barres défectueux',
            'Chute dans l\'eau',
            'Bouton d\'alimentation cassé',
            'Système très lent et plantages fréquents',
            'Connecteur de charge abîmé',
        ];

        $niveaux = ['mineure', 'majeure', 'critique'];
        $statuts = ['declaree', 'transmise_maintenance', 'en_maintenance', 'diagnostiquee', 'resolue', 'cloturee'];

        // Créer 15 pannes avec des statuts variés
        for ($i = 1; $i <= 15; $i++) {
            $equipement = $equipements->random();
            $agent = $agents->random();
            $statut = $statuts[array_rand($statuts)];
            
            $date_declaration = now()->subDays(rand(1, 60));
            $date_resolution = in_array($statut, ['resolue', 'cloturee']) ? (clone $date_declaration)->addDays(rand(2, 10)) : null;

            Panne::create([
                'equipement_id' => $equipement->id,
                'agent_id' => $agent->id,
                'gestionnaire_stock_id' => $gestionnaires->random()->id,
                'technicien_id' => $statut !== 'declaree' ? $techniciens->random()->id : null,
                'date_declaration' => $date_declaration,
                'description' => $descriptions[array_rand($descriptions)],
                'niveau_gravite' => $niveaux[array_rand($niveaux)],
                'diagnostic_technicien' => $statut !== 'declaree' ? 'Diagnostic pour ' . $equipement->nom : null,
                'action_realisee' => in_array($statut, ['resolue', 'cloturee']) ? 'Réparation effectuée' : null,
                'cout_reparation' => in_array($statut, ['resolue', 'cloturee']) ? rand(50, 500) : null,
                'statut' => $statut,
                'date_resolution' => $date_resolution,
                'decision_finale' => in_array($statut, ['resolue', 'cloturee']) ? 'repare' : 'en_attente',
            ]);

            // Mettre à jour l'état de l'équipement si la panne est active
            if (in_array($statut, ['declaree', 'transmise_maintenance', 'en_maintenance', 'diagnostiquee'])) {
                $equipement->update(['etat' => 'en_panne', 'statut_global' => 'en_panne']);
            }
        }

        echo "✅ 15 pannes créées avec succès !\n";
    }
}
