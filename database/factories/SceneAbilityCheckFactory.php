<?php

namespace Database\Factories;

use App\Enums\DndSkill;
use App\Models\Scene;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SceneAbilityCheck>
 */
class SceneAbilityCheckFactory extends Factory
{
    public function definition(): array
    {
        $dc = fake()->numberBetween(8, 18);

        return [
            'scene_id' => Scene::factory(),
            'skill' => fake()->randomElement(DndSkill::cases())->value,
            'subject' => fake()->words(3, true),
            'dc' => $dc,
            'dc_super' => null,
            'failure_text' => fake()->sentence(),
            'success_text' => fake()->sentence(),
            'super_success_text' => null,
            'sort_order' => 0,
        ];
    }
}
