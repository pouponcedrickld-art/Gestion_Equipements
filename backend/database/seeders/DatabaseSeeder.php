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
            MaintenanceSeeder::class,
            ConsommableSeeder::class,
            TransfertSeeder::class,
        ]);


        echo "\n🎉 Tous les seeders ont été exécutés avec succès !\n";
        echo "📋 Données de test créées :\n";
        echo "   - Rôles et permissions\n";
        echo "   - 4 Agences (1 générale + 3 sous-agences)\n";
        echo "   - 5 Utilisateurs avec différents rôles\n";
        echo "   - 9 Catégories d'équipements\n";
        echo "   - 6 Équipements de test avec QR codes\n";
        echo "   - 10 Maintenances de test (juin 2026)\n";
        echo "   - 12 Consommables associés\n";
        echo "   - 5 Transferts avec différents statuts\n";
    }
}
