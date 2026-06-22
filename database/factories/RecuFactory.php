<?php

namespace Database\Factories;

use App\Enums\RecuStatus;
use App\Models\Recu;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recu>
 */
class RecuFactory extends Factory
{
    protected $model = Recu::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'texte_source' => fake()->sentence(),
            'statut' => RecuStatus::EN_ATTENTE,
        ];
    }
}
