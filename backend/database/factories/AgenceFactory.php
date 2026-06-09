<?php

namespace Database\Factories;

use App\Models\Agence;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgenceFactory extends Factory
{
    protected $model = Agence::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['generale', 'sous_agence']),
            'parent_id' => null,
            'nom' => $this->faker->company(),
            'adresse' => $this->faker->streetAddress(),
            'ville' => $this->faker->city(),
            'responsable_id' => null,
            'gestionnaire_stock_id' => null,
            'statut' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
 