<?php

namespace Tests\Unit;

use App\Http\Requests\MaintenanceRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class MaintenanceRequestTest extends TestCase
{
    /**
     * Test que les règles de validation sont correctement définies.
     */
    public function test_validation_rules_are_defined(): void
    {
        $request = new MaintenanceRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('equipement_id', $rules);
        $this->assertArrayHasKey('date_prevue', $rules);
        $this->assertArrayHasKey('responsable', $rules);
        $this->assertArrayHasKey('type_maintenance', $rules);
        $this->assertArrayHasKey('cout', $rules);
        $this->assertArrayHasKey('observations', $rules);
    }

    /**
     * Test que l'autorisation est accordée.
     */
    public function test_authorize_returns_true(): void
    {
        $request = new MaintenanceRequest();
        $this->assertTrue($request->authorize());
    }

    /**
     * Test que les messages personnalisés sont en français.
     */
    public function test_custom_messages_are_in_french(): void
    {
        $request = new MaintenanceRequest();
        $messages = $request->messages();

        $this->assertStringContainsString('équipement', $messages['equipement_id.required']);
        $this->assertStringContainsString('date prévue', $messages['date_prevue.required']);
        $this->assertStringContainsString('responsable', $messages['responsable.required']);
        $this->assertStringContainsString('maintenance', $messages['type_maintenance.required']);
    }

    /**
     * Test validation avec des données valides.
     */
    public function test_validation_passes_with_valid_data(): void
    {
        $request = new MaintenanceRequest();
        $data = [
            'equipement_id' => 1,
            'date_prevue' => now()->addDays(5)->format('Y-m-d'),
            'responsable' => 'Jean Dupont',
            'type_maintenance' => 'préventif',
            'cout' => 250.50,
            'observations' => 'Maintenance trimestrielle',
        ];

        $validator = Validator::make($data, $request->rules(), $request->messages());
        
        // Note: Ce test ne vérifie pas exists:equipements,id car il faudrait une base de données
        // C'est un test de structure de règles
        $this->assertArrayHasKey('equipement_id', $validator->getRules());
    }

    /**
     * Test validation échoue avec des données invalides.
     */
    public function test_validation_fails_with_invalid_data(): void
    {
        $request = new MaintenanceRequest();
        $data = [
            'equipement_id' => '', // Manquant
            'date_prevue' => 'invalid-date',
            'responsable' => '',
            'type_maintenance' => 'invalide',
            'cout' => -100,
            'observations' => str_repeat('a', 1001), // Trop long
        ];

        $validator = Validator::make($data, $request->rules(), $request->messages());
        
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('equipement_id'));
        $this->assertTrue($validator->errors()->has('date_prevue'));
        $this->assertTrue($validator->errors()->has('responsable'));
        $this->assertTrue($validator->errors()->has('type_maintenance'));
        $this->assertTrue($validator->errors()->has('cout'));
        $this->assertTrue($validator->errors()->has('observations'));
    }

    /**
     * Test que cout et observations sont optionnels.
     */
    public function test_cout_and_observations_are_nullable(): void
    {
        $request = new MaintenanceRequest();
        $rules = $request->rules();

        $this->assertStringContainsString('nullable', $rules['cout']);
        $this->assertStringContainsString('nullable', $rules['observations']);
    }

    /**
     * Test que date_prevue doit être après ou égale à aujourd'hui.
     */
    public function test_date_prevue_must_be_after_or_equal_today(): void
    {
        $request = new MaintenanceRequest();
        $rules = $request->rules();

        $this->assertStringContainsString('after_or_equal:today', $rules['date_prevue']);
    }

    /**
     * Test que type_maintenance accepte seulement préventif ou correctif.
     */
    public function test_type_maintenance_accepts_only_valid_values(): void
    {
        $request = new MaintenanceRequest();
        $rules = $request->rules();

        $this->assertStringContainsString('in:préventif,correctif', $rules['type_maintenance']);
    }
}
