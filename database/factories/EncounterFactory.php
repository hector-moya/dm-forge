<?php

namespace Database\Factories;

use App\Models\GameSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Encounter>
 */
class EncounterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'game_session_id' => GameSession::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'difficulty' => fake()->randomElement(['easy', 'medium', 'hard', 'deadly']),
        ];
    }
}
