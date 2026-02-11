<?php

namespace Database\Factories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GameSession>
 */
class GameSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'title' => fake()->sentence(3),
            'session_number' => fake()->numberBetween(1, 20),
            'type' => 'sequential',
            'status' => 'draft',
        ];
    }

    public function prepared(): static
    {
        return $this->state(fn () => ['status' => 'prepared']);
    }

    public function running(): static
    {
        return $this->state(fn () => ['status' => 'running', 'started_at' => now()]);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed', 'started_at' => now()->subHours(3), 'ended_at' => now()]);
    }
}
