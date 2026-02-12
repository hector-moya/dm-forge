<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomLoot>
 */
class CustomLootFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'category' => fake()->randomElement(['equipment', 'magic_item', 'currency', 'other']),
            'rarity' => fake()->randomElement(['Common', 'Uncommon', 'Rare', 'Very Rare', 'Legendary']),
            'description' => fake()->sentence(),
            'value_gp' => fake()->randomFloat(2, 0.1, 5000),
        ];
    }
}
