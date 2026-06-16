<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer les agences pour l'assignation
        $agenceLome = \App\Models\Agence::where('nom', 'Agence Lomé-Centre')->first();

        // Gestionnaire Stock Général
        $gestionnaireGeneral = User::updateOrCreate(
            ['email' => 'gestionnaire@gestpark.local'],
            [
                'name' => 'Gestionnaire Stock Général',
                'password' => 'password123',
                'email_verified_at' => now(),
            ]
        );
        $gestionnaireGeneral->syncRoles(['gestionnaire_stock_general']);

        // Chef d'Agence Lomé
        $chefLome = User::updateOrCreate(
            ['email' => 'cheflome@gestpark.local'],
            [
                'name' => 'Chef Agence Lomé',
                'password' => 'password123',
                'agence_id' => $agenceLome?->id,
                'email_verified_at' => now(),
            ]
        );
        // Force update agence_id in case user already existed without it
        if ($chefLome->agence_id !== $agenceLome?->id) {
            $chefLome->agence_id = $agenceLome?->id;
            $chefLome->save();
        }
        $chefLome->syncRoles(['chef_agence']);

        // Gestionnaire Stock Lomé
        $gestionnaireLome = User::updateOrCreate(
            ['email' => 'stocklome@gestpark.local'],
            [
                'name' => 'Gestionnaire Stock Lomé',
                'password' => 'password123',
                'agence_id' => $agenceLome?->id,
                'email_verified_at' => now(),
            ]
        );
        // Force update agence_id in case user already existed without it
        if ($gestionnaireLome->agence_id !== $agenceLome?->id) {
            $gestionnaireLome->agence_id = $agenceLome?->id;
            $gestionnaireLome->save();
        }
        $gestionnaireLome->syncRoles(['gestionnaire_stock']);

        // Technicien
        $technicien = User::updateOrCreate(
            ['email' => 'technicien@gestpark.local'],
            [
                'name' => 'Technicien Maintenance',
                'password' => 'password123',
                'agence_id' => $agenceLome?->id,
                'email_verified_at' => now(),
            ]
        );
        // Force update agence_id in case user already existed without it
        if ($technicien->agence_id !== $agenceLome?->id) {
            $technicien->agence_id = $agenceLome?->id;
            $technicien->save();
        }
        $technicien->syncRoles(['technicien_maintenance']);
        
        // Créer Agent lié au Technicien
        \App\Models\Agent::updateOrCreate(
            ['user_id' => $technicien->id],
            [
                'matricule' => \App\Models\Agent::generateMatricule(),
                'nom' => 'Koffi',
                'prenom' => 'Maxime',
                'telephone' => '+228 90 00 00 00',
                'email' => $technicien->email,
                'poste' => 'Technicien Maintenance',
                'statut' => 'actif',
            ]
        );

        // Agent
        $agent = User::updateOrCreate(
            ['email' => 'agent@gestpark.local'],
            [
                'name' => 'Agent Terrain',
                'password' => 'password123',
                'agence_id' => $agenceLome?->id,
                'email_verified_at' => now(),
            ]
        );
        // Force update agence_id in case user already existed without it
        if ($agent->agence_id !== $agenceLome?->id) {
            $agent->agence_id = $agenceLome?->id;
            $agent->save();
        }
        $agent->syncRoles(['agent']);
        
        // Créer Agent lié à l'Agent Terrain
        \App\Models\Agent::updateOrCreate(
            ['user_id' => $agent->id],
            [
                'matricule' => \App\Models\Agent::generateMatricule(),
                'nom' => 'Amedegbe',
                'prenom' => 'Kodjo',
                'telephone' => '+228 91 11 11 11',
                'email' => $agent->email,
                'poste' => 'Agent Terrain',
                'statut' => 'actif',
            ]
        );

        echo "✅ Utilisateurs de test créés ou mis à jour avec succès !\n";
    }
}
