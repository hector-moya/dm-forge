<?php

namespace Database\Factories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorldEvent>
 */
class WorldEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'event_type' => fake()->randomElement(['faction_movement', 'consequence_resolved', 'npc_change', 'territory_change', 'custom']),
            'occurred_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'sort_order' => 0,
        ];
    }
}
