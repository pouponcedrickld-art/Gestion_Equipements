<?php

namespace Tests\Unit;

use App\Models\Equipement;
use App\Models\Maintenance;
use App\Models\Panne;
use App\Models\User;
use App\Services\MaintenanceWorkflowService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceWorkflowServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MaintenanceWorkflowService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MaintenanceWorkflowService();
    }

    /**
     * Test planification d'une maintenance préventive avec statut par défaut "planifiee"
     */
    public function test_planifier_preventive_creates_maintenance_with_default_status(): void
    {
        $equipement = Equipement::factory()->create();

        $data = [
            'equipement_id' => $equipement->id,
            'date_prevue' => now()->addDays(5)->format('Y-m-d'),
            'responsable' => 'Jean Dupont',
            'type_maintenance' => 'preventive',
        ];

        $maintenance = $this->service->planifierPreventive($data);

        $this->assertInstanceOf(Maintenance::class, $maintenance);
        $this->assertEquals('planifiee', $maintenance->statut);
        $this->assertEquals('preventive', $maintenance->type_maintenance);
        $this->assertEquals($equipement->id, $maintenance->equipement_id);
        $this->assertEquals('Jean Dupont', $maintenance->responsable);
    }

    /**
     * Test que planifierPreventive définit le type à "preventive" même si non fourni
     */
    public function test_planifier_preventive_sets_type_to_preventif_by_default(): void
    {
        $equipement = Equipement::factory()->create();

        $data = [
            'equipement_id' => $equipement->id,
            'date_prevue' => now()->addDays(5)->format('Y-m-d'),
            'responsable' => 'Jean Dupont',
        ];

        $maintenance = $this->service->planifierPreventive($data);

        $this->assertEquals('preventive', $maintenance->type_maintenance);
    }

    /**
     * Test récupération des maintenances par période
     */
    public function test_get_by_period_returns_maintenances_within_date_range(): void
    {
        $equipement = Equipement::factory()->create();

        // Créer des maintenances à différentes dates
        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'date_prevue' => '2024-03-15',
        ]);
        
        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'date_prevue' => '2024-03-20',
        ]);
        
        // Maintenance en dehors de la période
        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'date_prevue' => '2024-04-05',
        ]);

        $maintenances = $this->service->getByPeriod('2024-03-01', '2024-03-31');

        $this->assertCount(2, $maintenances);
        $this->assertTrue($maintenances->contains('date_prevue', '2024-03-15 00:00:00'));
        $this->assertTrue($maintenances->contains('date_prevue', '2024-03-20 00:00:00'));
    }

    /**
     * Test que getByPeriod charge les relations (eager loading)
     */
    public function test_get_by_period_loads_relations(): void
    {
        $equipement = Equipement::factory()->create();

        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'date_prevue' => '2024-03-15',
        ]);

        $maintenances = $this->service->getByPeriod('2024-03-01', '2024-03-31');

        $this->assertTrue($maintenances->first()->relationLoaded('equipement'));
        // Note: panne et technicienUser relations ne sont pas testées car les colonnes 
        // panne_id et technicien_id n'existent pas dans la migration actuelle
    }

    /**
     * Test filtrage par type de maintenance
     */
    public function test_get_by_period_filters_by_type_maintenance(): void
    {
        $equipement = Equipement::factory()->create();

        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'type_maintenance' => 'preventive',
            'date_prevue' => '2024-03-15',
        ]);
        
        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'type_maintenance' => 'corrective',
            'date_prevue' => '2024-03-20',
        ]);

        $filters = ['type_maintenance' => 'preventive'];
        $maintenances = $this->service->getByPeriod('2024-03-01', '2024-03-31', $filters);

        $this->assertCount(1, $maintenances);
        $this->assertEquals('preventive', $maintenances->first()->type_maintenance);
    }

    /**
     * Test filtrage par statut
     */
    public function test_get_by_period_filters_by_statut(): void
    {
        $equipement = Equipement::factory()->create();

        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'statut' => 'planifiee',
            'date_prevue' => '2024-03-15',
        ]);
        
        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'statut' => 'en_cours',
            'date_prevue' => '2024-03-20',
        ]);
        
        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'statut' => 'terminee',
            'date_prevue' => '2024-03-25',
        ]);

        $filters = ['statut' => 'en_cours'];
        $maintenances = $this->service->getByPeriod('2024-03-01', '2024-03-31', $filters);

        $this->assertCount(1, $maintenances);
        $this->assertEquals('en_cours', $maintenances->first()->statut);
    }

    /**
     * Test que getByPeriod trie par date_prevue croissante
     */
    public function test_get_by_period_orders_by_date_prevue_ascending(): void
    {
        $equipement = Equipement::factory()->create();

        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'date_prevue' => '2024-03-25',
        ]);
        
        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'date_prevue' => '2024-03-10',
        ]);
        
        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'date_prevue' => '2024-03-20',
        ]);

        $maintenances = $this->service->getByPeriod('2024-03-01', '2024-03-31');

        $this->assertEquals('2024-03-10 00:00:00', $maintenances[0]->date_prevue->format('Y-m-d H:i:s'));
        $this->assertEquals('2024-03-20 00:00:00', $maintenances[1]->date_prevue->format('Y-m-d H:i:s'));
        $this->assertEquals('2024-03-25 00:00:00', $maintenances[2]->date_prevue->format('Y-m-d H:i:s'));
    }

    /**
     * Test récupération d'une maintenance avec relations
     */
    public function test_get_maintenance_with_relations_loads_all_relations(): void
    {
        $equipement = Equipement::factory()->create();

        $maintenance = Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
        ]);

        $result = $this->service->getMaintenanceWithRelations($maintenance->id);

        $this->assertInstanceOf(Maintenance::class, $result);
        $this->assertTrue($result->relationLoaded('equipement'));
        $this->assertEquals($maintenance->id, $result->id);
        // Note: panne et technicienUser relations ne sont pas testées car les colonnes 
        // panne_id et technicien_id n'existent pas dans la migration actuelle
    }

    /**
     * Test que getMaintenanceWithRelations charge aussi la catégorie de l'équipement
     */
    public function test_get_maintenance_with_relations_loads_nested_relations(): void
    {
        $equipement = Equipement::factory()->create();
        $maintenance = Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
        ]);

        $result = $this->service->getMaintenanceWithRelations($maintenance->id);

        $this->assertTrue($result->equipement->relationLoaded('categorie'));
    }

    /**
     * Test que getMaintenanceWithRelations lève une exception si maintenance inexistante
     */
    public function test_get_maintenance_with_relations_throws_exception_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);
        
        $this->service->getMaintenanceWithRelations(99999);
    }

    /**
     * Test que les filtres vides ne sont pas appliqués
     */
    public function test_get_by_period_ignores_empty_filters(): void
    {
        $equipement = Equipement::factory()->create();

        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'type_maintenance' => 'preventive',
            'date_prevue' => '2024-03-15',
        ]);
        
        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'type_maintenance' => 'corrective',
            'date_prevue' => '2024-03-20',
        ]);

        // Filtres vides ou null ne doivent pas filtrer
        $filters = ['type_maintenance' => '', 'statut' => null];
        $maintenances = $this->service->getByPeriod('2024-03-01', '2024-03-31', $filters);

        $this->assertCount(2, $maintenances);
    }
}
