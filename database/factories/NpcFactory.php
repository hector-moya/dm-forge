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

    public function withStatBlock(): static
    {
        return $this->state([
            'race' => fake()->randomElement(['Human', 'Elf', 'Dwarf', 'Half-Orc', 'Tiefling']),
            'size' => 'Medium',
            'alignment' => fake()->randomElement(['Lawful Good', 'Neutral', 'Chaotic Evil', 'Lawful Neutral']),
            'armor_class' => fake()->numberBetween(10, 18),
            'armor_type' => fake()->randomElement(['Natural armor', 'Leather armor', 'Chain mail']),
            'hp_max' => fake()->numberBetween(10, 80),
            'hit_dice' => fake()->randomElement(['2d8+4', '4d8+8', '6d10+12']),
            'speed' => '30 ft.',
            'challenge_rating' => (string) fake()->randomElement(['1/4', '1/2', '1', '2', '3', '5']),
            'stats' => [
                'ability_scores' => [
                    'str' => fake()->numberBetween(8, 18),
                    'dex' => fake()->numberBetween(8, 18),
                    'con' => fake()->numberBetween(8, 18),
                    'int' => fake()->numberBetween(8, 18),
                    'wis' => fake()->numberBetween(8, 18),
                    'cha' => fake()->numberBetween(8, 18),
                ],
                'saving_throw_proficiencies' => ['str', 'con'],
                'skill_proficiencies' => ['athletics', 'perception'],
                'damage_resistances' => [],
                'damage_immunities' => [],
                'condition_immunities' => [],
                'senses' => 'passive Perception 12',
                'languages' => 'Common',
                'special_traits' => [],
                'actions' => [
                    ['name' => 'Longsword', 'description' => 'Melee Weapon Attack: +4 to hit, reach 5 ft., one target.'],
                ],
                'bonus_actions' => [],
                'reactions' => [],
                'legendary_actions' => [],
                'spellcasting' => null,
            ],
        ]);
    }
}
