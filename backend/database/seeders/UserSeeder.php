<?php

// database/seeders/UserSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Agence;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $agenceLome = Agence::where('nom', 'Agence Lomé-Centre')->first();
        $agenceKara = Agence::where('nom', 'Agence Kara')->first();
        $agenceSokode = Agence::where('nom', 'Agence Sokodé')->first();

        $gestionnaireGeneral = User::updateOrCreate(
            ['email' => 'gestionnaire@gestpark.local'],
            [
                'name' => 'Gestionnaire Stock Général',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        $gestionnaireGeneral->syncRoles(['gestionnaire_stock_general']);

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

        $technicien = User::updateOrCreate(
            ['email' => 'technicien@gestpark.local'],
            [
                'name' => 'Technicien Maintenance',
                'password' => bcrypt('password123'),
                'agence_id' => $agenceLome?->id,
                'email_verified_at' => now(),
            ]
        );
        $technicien->syncRoles(['technicien_maintenance']);

        $agent = User::updateOrCreate(
            ['email' => 'agent@gestpark.local'],
            [
                'name' => 'Agent Terrain',
                'password' => bcrypt('password123'),
                'agence_id' => $agenceLome?->id,
                'email_verified_at' => now(),
            ]
        );
        $agent->syncRoles(['agent']);

        $chefKara = User::updateOrCreate(
            ['email' => 'chefkara@gestpark.local'],
            [
                'name' => 'Chef Agence Kara',
                'password' => bcrypt('password123'),
                'agence_id' => $agenceKara?->id,
                'email_verified_at' => now(),
            ]
        );
        $chefKara->syncRoles(['chef_agence']);

        echo "✅ Utilisateurs de test créés ou mis à jour avec succès !\n";
    }
}