<?php

namespace Tests\Feature;

use App\Events\PanneDecisionIrrecuperable;
use App\Events\PanneDecisionReparee;
use App\Events\PanneDecisionRemplacementNecessaire;
use App\Models\Agence;
use App\Models\Equipement;
use App\Models\Panne;

use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

use Tests\TestCase;

class PanneDecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_decision_reparee_dispatch_event_and_create_history(): void
    {
        Notification::fake();

        $agence = Agence::factory()->create();

        $technicien = User::factory()->create(['agence_id' => $agence->id]);


        $equipement = Equipement::factory()->create([
            'agence_actuelle_id' => $agence->id,
            'statut_global' => 'en_panne',
        ]);

        $panne = Panne::factory()->create([
            'equipement_id' => $equipement->id,
            'technicien_id' => $technicien->id,
            'statut' => 'en_maintenance',
        ]);

        $this->actingAs($technicien, 'sanctum');

        $payload = [
            'decision' => 'reparee',
            'cout_estimatif' => 123.45,
            'commentaires' => 'OK',
        ];

        $this->postJson("/api/pannes/{$panne->id}/decider", $payload)->assertStatus(200);

        $this->assertDatabaseHas('panne_status_histories', [
            'panne_id' => $panne->id,
            'statut_nouveau' => 'resolue',
        ]);

        $this->assertDatabaseHas('pannes', [
            'id' => $panne->id,
            'statut' => 'resolue',
            'decision_finale' => 'reparee',
        ]);

        Notification::assertSentTimes(\App\Notifications\PanneDecisionRepareeNotification::class, 1);
        $this->assertEventDispatched(PanneDecisionReparee::class);
    }

    public function test_decision_remplacement_dispatch_event_and_create_history(): void
    {
        Notification::fake();

        $agence = Agence::factory()->create();

        $gestionnaire = User::factory()->create(['agence_id' => $agence->id]);

        $technicien = User::factory()->create(['agence_id' => $agence->id]);


        $equipement = Equipement::factory()->create([
            'agence_actuelle_id' => $agence->id,
            'statut_global' => 'en_panne',
        ]);

        $panne = Panne::factory()->create([
            'equipement_id' => $equipement->id,
            'technicien_id' => $technicien->id,
            'statut' => 'en_maintenance',
        ]);

        $this->actingAs($technicien, 'sanctum');

        $this->postJson("/api/pannes/{$panne->id}/decider", [
            'decision' => 'remplacement',
            'cout_estimatif' => 50,
            'commentaires' => 'Remplacement',
        ])->assertStatus(200);

        $this->assertDatabaseHas('panne_status_histories', [
            'panne_id' => $panne->id,
            'statut_nouveau' => 'resolue',
        ]);

        Notification::assertSentTimes(\App\Notifications\PanneDecisionRemplacementNotification::class, 1);
        $this->assertEventDispatched(PanneDecisionRemplacementNecessaire::class);
    }

    public function test_decision_irrecuperable_dispatch_event_and_create_history(): void
    {
        Notification::fake();

        $agence = Agence::factory()->create();


        $technicien = User::factory()->create(['agence_id' => $agence->id]);


        $equipement = Equipement::factory()->create([
            'agence_actuelle_id' => $agence->id,
            'statut_global' => 'en_panne',
        ]);

        $panne = Panne::factory()->create([
            'equipement_id' => $equipement->id,
            'technicien_id' => $technicien->id,
            'statut' => 'en_maintenance',
        ]);

        $this->actingAs($technicien, 'sanctum');

        $this->postJson("/api/pannes/{$panne->id}/decider", [
            'decision' => 'irrecuperable',
            'cout_estimatif' => 0,
            'commentaires' => 'HS',
        ])->assertStatus(200);

        $this->assertDatabaseHas('panne_status_histories', [
            'panne_id' => $panne->id,
            'statut_nouveau' => 'irrecuperable',
        ]);

        Notification::assertSentTimes(\App\Notifications\PanneDecisionIrrecuperableNotification::class, 1);
        $this->assertEventDispatched(PanneDecisionIrrecuperable::class);
    }
}


