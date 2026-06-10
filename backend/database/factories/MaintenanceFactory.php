<?php

namespace Database\Factories;

use App\Models\Equipement;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceFactory extends Factory
{
    protected $model = Maintenance::class;

    public function definition(): array
    {
        $datePrevue = $this->faker->dateTimeBetween('now', '+6 months');
        
        return [
            'equipement_id' => Equipement::factory(),
            'type_maintenance' => $this->faker->randomElement(['préventif', 'correctif']),
            'date_prevue' => $datePrevue,
            'date_debut' => null,
            'date_fin' => null,
            'duree_estimee' => $this->faker->numberBetween(1, 8),
            'responsable' => $this->faker->name(),
            'technicien_id' => User::factory(),
            'statut' => 'planifiee',
            'cout' => $this->faker->optional(0.7)->randomFloat(2, 50, 2000),
            'observations' => $this->faker->optional()->sentence(),
            'pieces_changees' => null,
            'rapport' => null,
        ];
    }

    /**
     * État pour maintenance préventive
     */
    public function preventif(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type_maintenance' => 'préventif',
            ];
        });
    }

    /**
     * État pour maintenance corrective
     */
    public function correctif(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type_maintenance' => 'correctif',
            ];
        });
    }

    /**
     * État pour maintenance planifiée
     */
    public function planifiee(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'statut' => 'planifiee',
                'date_debut' => null,
                'date_fin' => null,
            ];
        });
    }

    /**
     * État pour maintenance en cours
     */
    public function enCours(): static
    {
        return $this->state(function (array $attributes) {
            $dateDebut = $this->faker->dateTimeBetween('-2 days', 'now');
            
            return [
                'statut' => 'en_cours',
                'date_debut' => $dateDebut,
                'date_fin' => null,
            ];
        });
    }

    /**
     * État pour maintenance terminée
     */
    public function terminee(): static
    {
        return $this->state(function (array $attributes) {
            $dateDebut = $this->faker->dateTimeBetween('-30 days', '-5 days');
            $dateFin = $this->faker->dateTimeBetween($dateDebut, '-1 day');
            
            return [
                'statut' => 'terminee',
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'rapport' => $this->faker->optional(0.8)->paragraph(),
            ];
        });
    }

    /**
     * État pour maintenance avec coût élevé
     */
    public function coutEleve(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'cout' => $this->faker->randomFloat(2, 2000, 10000),
            ];
        });
    }

    /**
     * État pour maintenance dans les 7 prochains jours
     */
    public function prochaine7Jours(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'date_prevue' => $this->faker->dateTimeBetween('now', '+7 days'),
            ];
        });
    }
}
