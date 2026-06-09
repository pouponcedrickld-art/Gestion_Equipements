<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $agences = \App\Models\Agence::orderBy('id')->get();
        $agence1 = $agences[0] ?? null;
        $agence2 = $agences[1] ?? $agence1;

        // Gestionnaire Stock Général (peut ne pas avoir agence_id)
        $gestionnaireGeneral = User::create([
            'name' => 'Gestionnaire Stock Général',
            'email' => 'gestionnaire@gestpark.local',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'agence_id' => $agence1?->id,
            'actif' => true,
        ]);
        $gestionnaireGeneral->assignRole('gestionnaire_stock_general');

        // Chef d'Agence Lomé
        $chefLome = User::create([
            'name' => 'Chef Agence Lomé',
            'email' => 'cheflome@gestpark.local',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'agence_id' => $agence1?->id,
            'actif' => true,
        ]);
        $chefLome->assignRole('chef_agence');

        // Gestionnaire Stock Lomé
        $gestionnaireLome = User::create([
            'name' => 'Gestionnaire Stock Lomé',
            'email' => 'stocklome@gestpark.local',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'agence_id' => $agence1?->id,
            'actif' => true,
        ]);
        $gestionnaireLome->assignRole('gestionnaire_stock');

        // Technicien
        $technicien = User::create([
            'name' => 'Technicien Maintenance',
            'email' => 'technicien@gestpark.local',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'agence_id' => $agence2?->id,
            'actif' => true,
        ]);
        $technicien->assignRole('technicien_maintenance');

        // Agent
        $agent = User::create([
            'name' => 'Agent Terrain',
            'email' => 'agent@gestpark.local',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'agence_id' => $agence2?->id,
            'actif' => true,
        ]);
        $agent->assignRole('agent');


        echo "✅ Utilisateurs de test créés avec succès !\n";
    }
}
