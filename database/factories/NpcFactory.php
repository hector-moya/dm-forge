<?php

namespace Database\Factories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Npc>
 */
class NpcFactory extends Factory
{
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'name' => fake()->name(),
            'role' => fake()->randomElement(['Blacksmith', 'Innkeeper', 'Guard Captain', 'Merchant', 'Scholar']),
            'description' => fake()->sentence(),
            'personality' => fake()->sentence(),
            'motivation' => fake()->sentence(),
            'is_alive' => true,
        ];
    }

    public function dead(): static
    {
        return $this->state(['is_alive' => false]);
    }

    public function withVoice(): static
    {
        return $this->state([
            'voice_description' => fake()->sentence(),
            'speech_patterns' => fake()->sentence(),
            'catchphrases' => [fake()->sentence(), fake()->sentence()],
        ]);
    }
}
