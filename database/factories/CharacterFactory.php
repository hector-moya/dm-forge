<?php

namespace Database\Factories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Character>
 */
class CharacterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'name' => fake()->name(),
            'player_name' => fake()->firstName(),
            'class' => fake()->randomElement(['Fighter', 'Wizard', 'Rogue', 'Cleric', 'Ranger']),
            'level' => fake()->numberBetween(1, 20),
            'hp_max' => fake()->numberBetween(20, 150),
            'hp_current' => fake()->numberBetween(10, 150),
            'armor_class' => fake()->numberBetween(10, 22),
            'good_evil_score' => fake()->numberBetween(-10, 10),
            'law_chaos_score' => fake()->numberBetween(-10, 10),
            'alignment_label' => 'True Neutral',
        ];
    }
}
