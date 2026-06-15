<?php

namespace Tests\Unit;

use App\Models\Panne;
use App\Models\User;
use App\Services\PanneWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanneWorkflowServiceTest extends TestCase
{
    use RefreshDatabase;

    private PanneWorkflowService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PanneWorkflowService();
    }

    public function test_panne_can_be_declared(): void
    {
        $user = User::factory()->create();
        $panne = Panne::factory()->create([
            'statut' => null,
        ]);

        $result = $this->service->declarer($panne, $user);

        $this->assertEquals(PanneWorkflowService::STATUT_DECLAREE, $result->statut);
        $this->assertDatabaseHas('panne_status_histories', [
            'panne_id' => $panne->id,
            'statut_nouveau' => PanneWorkflowService::STATUT_DECLAREE,
            'created_by' => $user->id,
        ]);
    }

    public function test_panne_can_be_passed_to_in_progress(): void
    {
        $user = User::factory()->create();
        $panne = Panne::factory()->create([
            'statut' => PanneWorkflowService::STATUT_DECLAREE,
        ]);

        $result = $this->service->passerEnCours($panne, $user);

        $this->assertEquals(PanneWorkflowService::STATUT_EN_COURS, $result->statut);
    }
}
