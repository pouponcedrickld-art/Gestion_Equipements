<?php

namespace Database\Factories;

use App\Models\Categorie;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategorieFactory extends Factory
{
    protected $model = Categorie::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->randomElement([
                'Ordinateurs portables',
                'Ordinateurs de bureau',
                'Smartphones',
                'Tablettes',
                'Imprimantes',
                'Scanners',
                'Serveurs',
            ]),
            'description' => $this->faker->sentence(),
        ];
    }
}
