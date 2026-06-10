<?php

namespace Tests\Feature;

use App\Models\Agence;
use App\Models\Categorie;
use App\Models\Equipement;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MaintenanceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Agence $agence;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer une agence
        $this->agence = Agence::factory()->create([
            'type' => 'sous_agence',
        ]);

        // Créer les permissions
        Permission::create(['name' => 'maintenances.view_all']);
        Permission::create(['name' => 'maintenances.view_agence']);
        Permission::create(['name' => 'maintenances.planifier']);

        // Créer un rôle avec permissions (super_admin est autorisé sur la route)
        $role = Role::create(['name' => 'super_admin']);
        $role->givePermissionTo(['maintenances.view_all', 'maintenances.planifier']);

        // Créer un utilisateur
        $this->user = User::factory()->create([
            'agence_id' => $this->agence->id,
        ]);
        $this->user->assignRole($role);

        // Authentifier l'utilisateur
        Sanctum::actingAs($this->user);
    }

    /**
     * Test GET /api/maintenances avec filtrage par plage de dates
     */
    public function test_index_returns_maintenances_filtered_by_date_range(): void
    {
        $categorie = Categorie::factory()->create();
        $equipement = Equipement::factory()->create([
            'agence_actuelle_id' => $this->agence->id,
            'categorie_id' => $categorie->id,
        ]);

        // Créer des maintenances
        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'date_prevue' => '2024-03-15',
        ]);

        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'date_prevue' => '2024-03-20',
        ]);

        // Maintenance en dehors de la plage
        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'date_prevue' => '2024-04-05',
        ]);

        $response = $this->getJson('/api/maintenances?start_date=2024-03-01&end_date=2024-03-31');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'equipement_id', 'date_prevue', 'type_maintenance', 'statut']
                ],
                'meta' => ['total', 'per_page', 'current_page', 'last_page']
            ])
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    /**
     * Test GET /api/maintenances avec paramètre month
     */
    public function test_index_returns_maintenances_filtered_by_month(): void
    {
        $categorie = Categorie::factory()->create();
        $equipement = Equipement::factory()->create([
            'agence_actuelle_id' => $this->agence->id,
            'categorie_id' => $categorie->id,
        ]);

        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'date_prevue' => '2024-03-15',
        ]);

        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'date_prevue' => '2024-04-10',
        ]);

        $response = $this->getJson('/api/maintenances?month=2024-03');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Test GET /api/maintenances avec format de mois invalide
     */
    public function test_index_returns_error_with_invalid_month_format(): void
    {
        $response = $this->getJson('/api/maintenances?month=202403');

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Le format du mois doit être YYYY-MM');
    }

    /**
     * Test GET /api/maintenances avec filtres type_maintenance et statut
     */
    public function test_index_returns_maintenances_with_filters(): void
    {
        $categorie = Categorie::factory()->create();
        $equipement = Equipement::factory()->create([
            'agence_actuelle_id' => $this->agence->id,
            'categorie_id' => $categorie->id,
        ]);

        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'date_prevue' => '2024-03-15',
            'type_maintenance' => 'preventive',
            'statut' => 'planifiee',
        ]);

        Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
            'date_prevue' => '2024-03-20',
            'type_maintenance' => 'corrective',
            'statut' => 'en_cours',
        ]);

        $response = $this->getJson('/api/maintenances?start_date=2024-03-01&end_date=2024-03-31&type_maintenance=preventive&statut=planifiee');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Test GET /api/maintenances pagination à 100 par page
     */
    public function test_index_paginates_at_100_per_page(): void
    {
        $categorie = Categorie::factory()->create();
        $equipement = Equipement::factory()->create([
            'agence_actuelle_id' => $this->agence->id,
            'categorie_id' => $categorie->id,
        ]);

        // Créer 150 maintenances
        Maintenance::factory()->count(150)->create([
            'equipement_id' => $equipement->id,
            'date_prevue' => '2024-03-15',
        ]);

        $response = $this->getJson('/api/maintenances?start_date=2024-03-01&end_date=2024-03-31');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 150)
            ->assertJsonPath('meta.per_page', 100)
            ->assertJsonCount(100, 'data');
    }

    /**
     * Test GET /api/maintenances/{id} retourne les détails avec relations
     */
    public function test_show_returns_maintenance_with_relations(): void
    {
        $categorie = Categorie::factory()->create();
        $equipement = Equipement::factory()->create([
            'agence_actuelle_id' => $this->agence->id,
            'categorie_id' => $categorie->id,
        ]);

        $maintenance = Maintenance::factory()->create([
            'equipement_id' => $equipement->id,
        ]);

        $response = $this->getJson("/api/maintenances/{$maintenance->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'equipement_id',
                    'date_prevue',
                    'equipement' => ['id', 'reference'],
                ]
            ]);
    }

    /**
     * Test GET /api/maintenances/{id} retourne 404 si inexistante
     */
    public function test_show_returns_404_when_maintenance_not_found(): void
    {
        $response = $this->getJson('/api/maintenances/99999');

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Maintenance non trouvée');
    }

    /**
     * Test POST /api/maintenances crée une maintenance
     */
    public function test_store_creates_maintenance(): void
    {
        $categorie = Categorie::factory()->create();
        $equipement = Equipement::factory()->create([
            'agence_actuelle_id' => $this->agence->id,
            'categorie_id' => $categorie->id,
        ]);

        $data = [
            'equipement_id' => $equipement->id,
            'date_prevue' => now()->addDays(5)->format('Y-m-d'),
            'responsable' => 'Jean Dupont',
            'type_maintenance' => 'préventif',
            'cout' => 250.50,
            'observations' => 'Maintenance trimestrielle',
        ];

        $response = $this->postJson('/api/maintenances', $data);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Maintenance créée avec succès')
            ->assertJsonPath('data.equipement_id', $equipement->id)
            ->assertJsonPath('data.responsable', 'Jean Dupont')
            ->assertJsonPath('data.statut', 'planifiee');

        $this->assertDatabaseHas('maintenances', [
            'equipement_id' => $equipement->id,
            'responsable' => 'Jean Dupont',
            'statut' => 'planifiee',
        ]);
    }

    /**
     * Test POST /api/maintenances validation des données
     */
    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/maintenances', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['equipement_id', 'date_prevue', 'responsable', 'type_maintenance']);
    }

    /**
     * Test POST /api/maintenances validation date_prevue future
     */
    public function test_store_validates_date_prevue_is_future(): void
    {
        $categorie = Categorie::factory()->create();
        $equipement = Equipement::factory()->create([
            'agence_actuelle_id' => $this->agence->id,
            'categorie_id' => $categorie->id,
        ]);

        $data = [
            'equipement_id' => $equipement->id,
            'date_prevue' => now()->subDays(5)->format('Y-m-d'),
            'responsable' => 'Jean Dupont',
            'type_maintenance' => 'préventif',
        ];

        $response = $this->postJson('/api/maintenances', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date_prevue']);
    }

    /**
     * Test que les endpoints nécessitent une authentification
     */
    public function test_endpoints_require_authentication(): void
    {
        // Annuler l'authentification
        $this->app['auth']->forgetGuards();

        $response = $this->getJson('/api/maintenances');
        $response->assertStatus(401);

        $response = $this->getJson('/api/maintenances/1');
        $response->assertStatus(401);

        $response = $this->postJson('/api/maintenances', []);
        $response->assertStatus(401);
    }
}
