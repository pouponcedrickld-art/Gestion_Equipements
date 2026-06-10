<?php

// database/seeders/RoleSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'agences.view_all',
            'agences.view_own',
            'agences.create',
            'agences.edit',
            'agences.delete',
            'users.view_all',
            'users.view_agence',
            'users.view_own',
            'users.create',
            'users.edit',
            'users.delete',
            'equipements.view_global',
            'equipements.view_agence',
            'equipements.view_own',
            'equipements.create',
            'equipements.edit',
            'equipements.delete',
            'equipements.import',
            'equipements.generer_qr',
            'transferts.view_all',
            'transferts.view_agence',
            'transferts.demander',
            'transferts.approuver',
            'transferts.expedier',
            'transferts.recevoir',
            'demandes.view_all',
            'demandes.view_agence',
            'demandes.creer',
            'demandes.traiter',
            'demandes.annuler',
            'affectations.view_all',
            'affectations.view_agence',
            'affectations.view_own',
            'affectations.creer',
            'affectations.retourner',
            'affectations.annuler',
            'pannes.view_all',
            'pannes.view_agence',
            'pannes.view_own',
            'pannes.declarer',
            'pannes.recevoir',
            'pannes.transmettre_maintenance',
            'pannes.diagnostiquer',
            'pannes.resoudre',
            'maintenances.view_all',
            'maintenances.view_agence',
            'maintenances.planifier',
            'maintenances.realiser',
            'maintenances.cloturer',
            'pertes.view_all',
            'pertes.view_agence',
            'pertes.view_own',
            'pertes.declarer',
            'pertes.valider',
            'pertes.cloturer',
            'mouvements.view_all',
            'mouvements.view_agence',
            'mouvements.view_own',
            'notifications.view_all',
            'notifications.view_own',
            'notifications.envoyer',
            'notifications.configurer',
            'rapports.view_global',
            'rapports.view_agence',
            'rapports.generer',
            'rapports.exporter',
            'settings.view',
            'settings.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'api']);
        $superAdmin->syncPermissions(Permission::all());

        $gestionnaireGeneral = Role::firstOrCreate(['name' => 'gestionnaire_stock_general', 'guard_name' => 'api']);
        $gestionnaireGeneral->syncPermissions([
            'agences.view_all',
            'agences.view_own',
            'users.view_all',
            'users.create',
            'users.edit',
            'equipements.view_global',
            'equipements.create',
            'equipements.edit',
            'equipements.import',
            'equipements.generer_qr',
            'transferts.view_all',
            'transferts.approuver',
            'transferts.expedier',
            'demandes.view_all',
            'demandes.traiter',
            'affectations.view_all',
            'pannes.view_all',
            'maintenances.view_all',
            'mouvements.view_all',
            'notifications.view_all',
            'notifications.envoyer',
            'notifications.configurer',
            'rapports.view_global',
            'rapports.generer',
            'rapports.exporter',
            'settings.view',
            'settings.edit',
        ]);

        $chefAgence = Role::firstOrCreate(['name' => 'chef_agence', 'guard_name' => 'api']);
        $chefAgence->syncPermissions([
            'agences.view_own',
            'users.view_agence',
            'users.create',
            'users.edit',
            'equipements.view_agence',
            'transferts.view_agence',
            'transferts.demander',
            'demandes.view_agence',
            'demandes.creer',
            'demandes.annuler',
            'affectations.view_agence',
            'pannes.view_agence',
            'maintenances.view_agence',
            'mouvements.view_agence',
            'notifications.view_own',
            'rapports.view_agence',
            'rapports.generer',
        ]);

        $gestionnaireStock = Role::firstOrCreate(['name' => 'gestionnaire_stock', 'guard_name' => 'api']);
        $gestionnaireStock->syncPermissions([
            'agences.view_own',
            'users.view_agence',
            'equipements.view_agence',
            'equipements.edit',
            'transferts.view_agence',
            'transferts.recevoir',
            'demandes.view_agence',
            'affectations.view_agence',
            'affectations.creer',
            'affectations.retourner',
            'affectations.annuler',
            'pannes.view_agence',
            'pannes.recevoir',
            'pannes.transmettre_maintenance',
            'pertes.view_agence',
            'pertes.declarer',
            'pertes.valider',
            'pertes.cloturer',
            'mouvements.view_agence',
            'notifications.view_own',
            'rapports.view_agence',
            'rapports.generer',
        ]);

        $technicien = Role::firstOrCreate(['name' => 'technicien_maintenance', 'guard_name' => 'api']);
        $technicien->syncPermissions([
            'equipements.view_agence',
            'pannes.view_agence',
            'pannes.diagnostiquer',
            'pannes.resoudre',
            'maintenances.view_agence',
            'maintenances.planifier',
            'maintenances.realiser',
            'maintenances.cloturer',
            'mouvements.view_own',
            'notifications.view_own',
        ]);

        $agent = Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'api']);
        $agent->syncPermissions([
            'users.view_own',
            'equipements.view_own',
            'affectations.view_own',
            'pannes.view_own',
            'pannes.declarer',
            'pertes.view_own',
            'pertes.declarer',
            'mouvements.view_own',
            'notifications.view_own',
        ]);

        $admin = \App\Models\User::firstOrCreate(
            ['email' => 'admin@gestpark.local'],
            [
                'name' => 'Administrateur GESTPARK',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('super_admin');

        echo "✅ Rôles et permissions créés avec succès !\n";
        echo "👤 Admin par défaut : admin@gestpark.local / password123\n";
    }
}