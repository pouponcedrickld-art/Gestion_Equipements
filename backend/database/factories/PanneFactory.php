<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\Equipement;
use App\Models\Panne;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PanneFactory extends Factory
{
    protected $model = Panne::class;

    public function definition(): array
    {
        return [
            'equipement_id' => Equipement::factory(),
            'agent_id' => Agent::factory(),
            'date_declaration' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'description' => $this->faker->sentence(),
            'niveau_gravite' => $this->faker->randomElement(['mineure', 'majeure', 'critique']),
            'photos' => null,
            'statut' => $this->faker->randomElement(['declaree', 'en_cours', 'en_maintenance', 'resolue', 'irrecuperable']),
            'date_resolution' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'solution' => $this->faker->optional()->sentence(),
        ];
    }
}
