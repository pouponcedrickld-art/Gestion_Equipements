<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AgenceSeeder::class,
            UserSeeder::class,
            AgentSeeder::class,
            CategorieSeeder::class,
            EquipementSeeder::class,
            ConsommableSeeder::class,
            AffectationSeeder::class,
            PanneSeeder::class,
            MaintenanceSeeder::class,
            TransfertSeeder::class,
            MouvementSeeder::class,
            PerteSeeder::class,
            NotificationSeeder::class,
        ]);

        echo "\n🎉 Tous les seeders ont été exécutés avec succès !\n";
        echo "📋 Données de test riches créées pour le Dashboard.\n";
    }
}
