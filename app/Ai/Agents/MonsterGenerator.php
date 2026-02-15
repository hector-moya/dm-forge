<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[UseCheapestModel]
#[Temperature(0.8)]
class MonsterGenerator implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are an expert D&D 5e monster designer. Generate a detailed, mechanically balanced custom monster stat block.

Guidelines:
- Ensure ability scores, AC, HP, and CR are internally consistent and balanced
- Hit dice should match the monster's size (d6 Tiny, d8 Small/Medium, d10 Large, d12 Huge, d20 Gargantuan)
- XP should correspond to the challenge rating using the standard 5e XP table
- Special abilities should be flavorful and mechanically interesting
- Actions should include at least one attack with a clear description
- Notes should contain roleplay hooks, habitat, or encounter suggestions
PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->required()
                ->description('Name of the monster'),
            'size' => $schema->string()->required()
                ->enum(['Tiny', 'Small', 'Medium', 'Large', 'Huge', 'Gargantuan'])
                ->description('Size category'),
            'type' => $schema->string()->required()
                ->description('Creature type (e.g., beast, undead, fiend, dragon, humanoid)'),
            'subtype' => $schema->string()
                ->description('Subtype if applicable (e.g., goblinoid, shapechanger)'),
            'alignment' => $schema->string()->required()
                ->description('Alignment (e.g., chaotic evil, lawful good, unaligned)'),
            'armor_class' => $schema->integer()->required()->min(1)->max(30)
                ->description('Armor class value'),
            'armor_class_type' => $schema->string()
                ->description('Source of AC (e.g., natural armor, chain mail)'),
            'hit_points' => $schema->integer()->required()->min(1)
                ->description('Average hit points'),
            'hit_dice' => $schema->string()->required()
                ->description('Hit dice expression (e.g., 4d8+8, 12d10+60)'),
            'strength' => $schema->integer()->required()->min(1)->max(30)
                ->description('Strength score'),
            'dexterity' => $schema->integer()->required()->min(1)->max(30)
                ->description('Dexterity score'),
            'constitution' => $schema->integer()->required()->min(1)->max(30)
                ->description('Constitution score'),
            'intelligence' => $schema->integer()->required()->min(1)->max(30)
                ->description('Intelligence score'),
            'wisdom' => $schema->integer()->required()->min(1)->max(30)
                ->description('Wisdom score'),
            'charisma' => $schema->integer()->required()->min(1)->max(30)
                ->description('Charisma score'),
            'challenge_rating' => $schema->number()->required()
                ->description('Challenge rating (0-30, can be decimal like 0.25, 0.5)'),
            'xp' => $schema->integer()->required()
                ->description('Experience points awarded'),
            'special_abilities' => $schema->array()->items(
                $schema->object([
                    'name' => $schema->string()->required()
                        ->description('Ability name'),
                    'desc' => $schema->string()->required()
                        ->description('Ability description'),
                ])
            )->description('Special abilities and traits'),
            'actions' => $schema->array()->items(
                $schema->object([
                    'name' => $schema->string()->required()
                        ->description('Action name'),
                    'desc' => $schema->string()->required()
                        ->description('Action description including attack bonus and damage'),
                ])
            )->required()->description('Combat actions'),
            'languages' => $schema->string()
                ->description('Languages the monster speaks or understands'),
            'notes' => $schema->string()
                ->description('Roleplay hooks, habitat, behavior, and encounter suggestions'),
        ];
    }
}
