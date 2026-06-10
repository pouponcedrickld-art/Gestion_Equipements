<?php

// database/seeders/DatabaseSeeder.php
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
            TransfertSeeder::class,
            AffectationSeeder::class,
            PanneSeeder::class,
            MaintenanceSeeder::class,
            PerteSeeder::class,
            MouvementSeeder::class,
            NotificationSeeder::class,
            TestKanbanSeeder::class,
        ]);

        echo "\n🎉 Tous les seeders ont été exécutés avec succès !\n";
    }
}