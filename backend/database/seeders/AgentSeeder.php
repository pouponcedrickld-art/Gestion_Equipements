<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agents = [
            [
                'matricule' => 'AG-2026-001',
                'nom' => 'KOFFI',
                'prenom' => 'Jean-Marc',
                'telephone' => '+225 0707070701',
                'email' => 'jm.koffi@agence.com',
                'direction' => 'Opérations',
                'service' => 'Logistique',
                'poste' => 'Responsable Transport',
                'statut' => 'actif',
            ],
            [
                'matricule' => 'AG-2026-002',
                'nom' => 'DIALLO',
                'prenom' => 'Aminata',
                'telephone' => '+225 0707070702',
                'email' => 'a.diallo@agence.com',
                'direction' => 'Finance',
                'service' => 'Comptabilité',
                'poste' => 'Comptable Senior',
                'statut' => 'actif',
            ],
            [
                'matricule' => 'AG-2026-003',
                'nom' => 'TRAORE',
                'prenom' => 'Bakary',
                'telephone' => '+225 0707070703',
                'email' => 'b.traore@agence.com',
                'direction' => 'Informatique',
                'service' => 'Support IT',
                'poste' => 'Technicien Réseaux',
                'statut' => 'actif',
            ],
            [
                'matricule' => 'AG-2026-004',
                'nom' => 'SYLLA',
                'prenom' => 'Fatoumata',
                'telephone' => '+225 0707070704',
                'email' => 'f.sylla@agence.com',
                'direction' => 'Ressources Humaines',
                'service' => 'Paie',
                'poste' => 'Gestionnaire RH',
                'statut' => 'actif',
            ],
            [
                'matricule' => 'AG-2026-005',
                'nom' => 'YESSIA',
                'prenom' => 'Marcel',
                'telephone' => '+225 0707070705',
                'email' => 'm.yessia@agence.com',
                'direction' => 'Technique',
                'service' => 'Maintenance',
                'poste' => 'Ingénieur Maintenance',
                'statut' => 'actif',
            ],
            [
                'matricule' => 'AG-2026-006',
                'nom' => 'OUEDRAOGO',
                'prenom' => 'Idrissa',
                'telephone' => '+225 0707070706',
                'email' => 'i.ouedraogo@agence.com',
                'direction' => 'Commerciale',
                'service' => 'Ventes',
                'poste' => 'Agent de Terrain',
                'statut' => 'actif',
            ],
        ];

        foreach ($agents as $agentData) {
            \App\Models\Agent::updateOrCreate(
                ['matricule' => $agentData['matricule']],
                $agentData
            );
        }
    }
}
