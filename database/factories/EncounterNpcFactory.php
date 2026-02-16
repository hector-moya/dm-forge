<?php

namespace Database\Factories;

use App\Models\Encounter;
use App\Models\Npc;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EncounterNpc>
 */
class EncounterNpcFactory extends Factory
{
    public function definition(): array
    {
        return [
            'encounter_id' => Encounter::factory(),
            'npc_id' => Npc::factory(),
            'name' => fake()->name(),
            'hp_max' => fake()->numberBetween(10, 50),
            'armor_class' => fake()->numberBetween(10, 18),
        ];
    }
}
