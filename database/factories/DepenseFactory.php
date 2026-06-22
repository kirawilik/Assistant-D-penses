<?php

namespace Database\Factories;

use App\Enums\DepenseCategorie;
use App\Models\Depense;
use App\Models\Recu;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepenseFactory extends Factory
{
    protected $model = Depense::class;

    public function definition(): array
    {
        return [
            'recu_id' => Recu::factory(),
            'libelle' => fake()->word(),
            'quantite' => fake()->numberBetween(1, 5),
            'prix_unitaire' => fake()->randomFloat(2, 1, 50),
            'categorie' => fake()->randomElement(DepenseCategorie::cases()),
        ];
    }
}
