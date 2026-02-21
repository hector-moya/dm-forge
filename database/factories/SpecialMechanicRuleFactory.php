<?php

namespace Database\Factories;

use App\Models\SpecialMechanic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SpecialMechanicRule>
 */
class SpecialMechanicRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'special_mechanic_id' => SpecialMechanic::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'notes' => null,
        ];
    }
}
