<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomMonster>
 */
class CustomMonsterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'size' => fake()->randomElement(['Tiny', 'Small', 'Medium', 'Large', 'Huge']),
            'type' => fake()->randomElement(['humanoid', 'beast', 'undead', 'fiend', 'dragon']),
            'armor_class' => fake()->numberBetween(10, 20),
            'hit_points' => fake()->numberBetween(5, 200),
            'challenge_rating' => fake()->randomFloat(2, 0, 20),
            'xp' => fake()->numberBetween(10, 10000),
        ];
    }
}
