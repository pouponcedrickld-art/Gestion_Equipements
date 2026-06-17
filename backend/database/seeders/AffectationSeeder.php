<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Affectation;
use App\Models\Agent;
use App\Models\Equipement;
use App\Models\User;

class AffectationSeeder extends Seeder
{
    public function run(): void
    {
        $agents = Agent::all();
        $equipements = Equipement::where('etat', 'en_service')
                                  ->whereIn('statut_global', ['en_stock_general', 'en_stock_local', 'affecte'])
                                  ->get();
        $gestionnaires = User::role(['gestionnaire_stock', 'gestionnaire_stock_general'])->get();

        if ($agents->isEmpty() || $equipements->isEmpty()) {
            echo "⚠️ Données manquantes pour les affectations.\n";
            return;
        }

        // Créer 12 affectations
        for ($i = 1; $i <= 12; $i++) {
            $agent = $agents->random();
            $equipement = $equipements->random();
            
            // Éviter d'affecter le même équipement plusieurs fois comme "actif"
            $isAlreadyAffecte = Affectation::where('equipement_id', $equipement->id)
                                          ->where('statut', 'active')
                                          ->exists();
            
            $statut = ($i > 4 && !$isAlreadyAffecte) ? 'active' : 'retournee';
            $date_affectation = now()->subDays(rand(10, 100));
            $date_retour_prevu = (clone $date_affectation)->addDays(rand(30, 90));
            $date_retour_effectif = ($statut === 'retournee') ? (clone $date_affectation)->addDays(rand(5, 25)) : null;

            Affectation::create([
                'agent_id' => $agent->id,
                'equipement_id' => $equipement->id,
                'date_affectation' => $date_affectation,
                'date_retour_prevu' => $date_retour_prevu,
                'date_retour_effectif' => $date_retour_effectif,
                'affecte_par' => $gestionnaires->random()->id,
                'etat_retour' => ($statut === 'retournee') ? 'bon' : null,
                'observations' => 'Affectation de test ' . $i,
                'statut' => $statut,
            ]);

            if ($statut === 'active') {
                $equipement->update(['statut_global' => 'affecte']);
            }
        }

        echo "✅ 12 affectations créées avec succès !\n";
    }
}
