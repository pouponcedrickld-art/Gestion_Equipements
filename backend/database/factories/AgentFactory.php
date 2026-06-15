<?php

namespace Database\Factories;

use App\Models\Agent;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgentFactory extends Factory
{
    protected $model = Agent::class;

    public function definition(): array
    {
        return [
            'matricule' => 'AG-' . $this->faker->unique()->numberBetween(1000, 9999),
            'nom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),
            'telephone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'poste' => $this->faker->jobTitle(),
            'statut' => $this->faker->randomElement(['actif', 'inactif']),
            'photo' => null,
            'user_id' => null,
        ];
    }
}
