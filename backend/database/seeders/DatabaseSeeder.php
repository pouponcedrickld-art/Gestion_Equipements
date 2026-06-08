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
            CategorieSeeder::class,
            EquipementSeeder::class,
        ]);

        echo "\n🎉 Tous les seeders ont été exécutés avec succès !\n";
    }
}
