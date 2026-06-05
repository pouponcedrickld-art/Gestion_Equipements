<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Gestionnaire Stock Général
        $gestionnaireGeneral = User::create([
            'name' => 'Gestionnaire Stock Général',
            'email' => 'gestionnaire@gestpark.local',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $gestionnaireGeneral->assignRole('gestionnaire_stock_general');

        // Chef d'Agence Lomé
        $chefLome = User::create([
            'name' => 'Chef Agence Lomé',
            'email' => 'cheflome@gestpark.local',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $chefLome->assignRole('chef_agence');

        // Gestionnaire Stock Lomé
        $gestionnaireLome = User::create([
            'name' => 'Gestionnaire Stock Lomé',
            'email' => 'stocklome@gestpark.local',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $gestionnaireLome->assignRole('gestionnaire_stock');

        // Technicien
        $technicien = User::create([
            'name' => 'Technicien Maintenance',
            'email' => 'technicien@gestpark.local',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $technicien->assignRole('technicien_maintenance');

        // Agent
        $agent = User::create([
            'name' => 'Agent Terrain',
            'email' => 'agent@gestpark.local',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $agent->assignRole('agent');

        echo "✅ Utilisateurs de test créés avec succès !\n";
    }
}
