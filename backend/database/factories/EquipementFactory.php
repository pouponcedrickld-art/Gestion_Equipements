<?php

namespace Database\Factories;

use App\Models\Agence;
use App\Models\Categorie;
use App\Models\Equipement;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipementFactory extends Factory
{
    protected $model = Equipement::class;

    public function definition(): array
    {
        return [
            'reference' => 'EQ-' . $this->faker->unique()->numberBetween(1000, 9999),
            'numero_serie' => strtoupper($this->faker->bothify('??######')),
            'imei' => $this->faker->optional()->numerify('###############'),
            'code_inventaire' => 'INV-' . $this->faker->numberBetween(1000, 9999),
            'marque' => $this->faker->randomElement(['HP', 'Dell', 'Lenovo', 'Apple', 'Samsung']),
            'modele' => $this->faker->randomElement(['ProBook 450', 'Latitude 5420', 'ThinkPad T14', 'MacBook Pro', 'Galaxy Tab']),
            'categorie_id' => Categorie::factory(),
            'fournisseur' => $this->faker->company(),
            'date_acquisition' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'prix_achat' => $this->faker->randomFloat(2, 500, 5000),
            'garantie_date_fin' => $this->faker->dateTimeBetween('now', '+3 years'),
            'etat' => $this->faker->randomElement(['neuf', 'bon', 'moyen', 'mauvais']),
            'localisation' => $this->faker->optional()->city(),
            'agence_proprietaire_id' => Agence::factory(),
            'agence_actuelle_id' => function (array $attributes) {
                return $attributes['agence_proprietaire_id'];
            },
            'statut_global' => $this->faker->randomElement(['en_stock_general', 'en_transit', 'en_stock_local', 'affecte', 'en_panne', 'reforme']),
            'photo' => null,
            'qr_code' => null,
        ];
    }

    /**
     * État pour équipement avec garantie expirant dans exactement 30 jours
     */
    public function garantieExpireDans30Jours(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'garantie_date_fin' => now()->addDays(30),
            ];
        });
    }
}
