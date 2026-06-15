<?php

namespace Tests\Unit;

use App\Models\Panne;
use App\Models\User;
use App\Services\PanneDecisionService;
use App\Services\PanneWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanneDecisionServiceTest extends TestCase
{
    use RefreshDatabase;

    private PanneDecisionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PanneDecisionService(new PanneWorkflowService());
    }

    public function test_panne_can_be_marked_as_repaired(): void
    {
        $user = User::factory()->create();
        $panne = Panne::factory()->create([
            'statut' => PanneWorkflowService::STATUT_EN_MAINTENANCE,
        ]);

        $result = $this->service->repare($panne, $user, ['commentaires' => 'Réparé']);

        $this->assertEquals(PanneWorkflowService::STATUT_RESOLUE, $result->statut);
        $this->assertEquals('reparee', $result->decision_finale);
    }

    public function test_panne_can_be_marked_as_irreparable(): void
    {
        $user = User::factory()->create();
        $panne = Panne::factory()->create([
            'statut' => PanneWorkflowService::STATUT_EN_MAINTENANCE,
        ]);

        $result = $this->service->irrecuperable($panne, $user, []);

        $this->assertEquals(PanneWorkflowService::STATUT_IRRECUPERABLE, $result->statut);
    }
}
