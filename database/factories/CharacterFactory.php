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

    public function withFullSheet(): static
    {
        return $this->state([
            'race' => fake()->randomElement(['Human', 'Elf', 'Dwarf', 'Half-Elf', 'Gnome']),
            'background' => fake()->randomElement(['Soldier', 'Noble', 'Outlander', 'Criminal', 'Acolyte']),
            'speed' => 30,
            'proficiency_bonus' => 2,
            'experience_points' => 0,
            'stats' => [
                'ability_scores' => [
                    'str' => fake()->numberBetween(8, 18),
                    'dex' => fake()->numberBetween(8, 18),
                    'con' => fake()->numberBetween(8, 18),
                    'int' => fake()->numberBetween(8, 18),
                    'wis' => fake()->numberBetween(8, 18),
                    'cha' => fake()->numberBetween(8, 18),
                ],
                'saving_throw_proficiencies' => ['dex', 'wis'],
                'skill_proficiencies' => ['athletics', 'perception'],
                'other_proficiencies' => 'All armor, shields, simple and martial weapons',
                'languages' => 'Common',
                'equipment' => ['Longsword', 'Shield'],
                'features_traits' => [
                    ['name' => 'Second Wind', 'description' => 'Bonus action to regain HP.'],
                ],
                'spells' => [
                    'spellcasting_ability' => null,
                    'spell_save_dc' => null,
                    'spell_attack_bonus' => null,
                    'cantrips' => [],
                    'spells_by_level' => [],
                ],
            ],
        ]);
    }
}
