<?php

namespace Database\Factories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Puzzle>
 */
class PuzzleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'solution' => fake()->sentence(),
            'difficulty' => fake()->randomElement(['easy', 'medium', 'hard']),
            'puzzle_type' => fake()->randomElement(['riddle', 'logic', 'physical', 'cipher', 'pattern']),
            'is_solved' => false,
        ];
    }

    public function solved(): static
    {
        return $this->state(['is_solved' => true]);
    }

    public function withHints(): static
    {
        return $this->state([
            'hint_tier_1' => fake()->sentence(),
            'hint_tier_2' => fake()->sentence(),
            'hint_tier_3' => fake()->sentence(),
        ]);
    }
}
