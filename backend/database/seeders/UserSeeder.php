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
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        $gestionnaireGeneral->syncRoles(['gestionnaire_stock_general']);

        // Chef d'Agence Lomé
        $chefLome = User::updateOrCreate(
            ['email' => 'cheflome@gestpark.local'],
            [
                'name' => 'Chef Agence Lomé',
                'password' => bcrypt('password123'),
                'agence_id' => $agenceLome?->id,
                'email_verified_at' => now(),
            ]
        );
        $chefLome->syncRoles(['chef_agence']);

        // Gestionnaire Stock Lomé
        $gestionnaireLome = User::updateOrCreate(
            ['email' => 'stocklome@gestpark.local'],
            [
                'name' => 'Gestionnaire Stock Lomé',
                'password' => bcrypt('password123'),
                'agence_id' => $agenceLome?->id,
                'email_verified_at' => now(),
            ]
        );
        $gestionnaireLome->syncRoles(['gestionnaire_stock']);

        // Technicien
        $technicien = User::updateOrCreate(
            ['email' => 'technicien@gestpark.local'],
            [
                'name' => 'Technicien Maintenance',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        $technicien->syncRoles(['technicien_maintenance']);

        // Agent
        $agent = User::updateOrCreate(
            ['email' => 'agent@gestpark.local'],
            [
                'name' => 'Agent Terrain',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        $agent->syncRoles(['agent']);

        echo "✅ Utilisateurs de test créés ou mis à jour avec succès !\n";
    }
}
