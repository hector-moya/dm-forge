<?php

namespace App\Ai\Agents;

use App\Ai\Concerns\HasCampaignContext;
use App\Ai\Tools\LookupFaction;
use App\Ai\Tools\LookupLocation;
use App\Ai\Tools\LookupNpc;
use App\Models\Campaign;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

#[UseCheapestModel]
#[Temperature(0.8)]
class NpcGenerator implements Agent, HasStructuredOutput, HasTools
{
    use HasCampaignContext, Promptable;

    public function __construct(
        protected Campaign $campaign,
    ) {}

    public function instructions(): Stringable|string
    {
        $context = $this->buildCampaignContext();

        return <<<PROMPT
You are a creative D&D NPC designer. Generate a detailed, memorable NPC with a complete D&D 5e stat block that fits naturally into the campaign world.

{$context}

Guidelines:
- Create NPCs with distinct personalities, clear motivations, and memorable quirks
- Voice descriptions should help the DM roleplay the NPC (accent, cadence, pitch, mannerisms)
- Speech patterns should describe HOW the NPC talks (formal, slang, repetitive, poetic, etc.)
- Catchphrases should be 2-4 short, memorable phrases the NPC frequently uses
- Use the lookup tools to reference existing NPCs, locations, and factions when relevant
- The NPC should feel organic within the campaign's theme and tone
- Consider how existing faction dynamics shape this NPC's backstory and allegiances
- Backstory should include past decisions and events that shaped who they are
- Suggest a faction and location if relevant to the campaign

D&D 5e Stat Block Guidelines:
- Ability scores should be appropriate for the NPC's role and CR (typically 1-20 for most NPCs)
- Challenge Rating guides overall power level: 0-1 for ordinary folk, 1-5 for trained combatants, 5+ for powerful enemies
- Hit points should align with the hit dice expression
- Include actions the NPC can take in combat — at minimum a basic attack
- Only include spellcasting if the NPC is a spellcaster
- Include legendary actions only for boss-tier NPCs (CR 10+)
- Damage resistances/immunities should be appropriate to the NPC's race and type
PROMPT;
    }

    public function tools(): iterable
    {
        return [
            new LookupNpc($this->campaign),
            new LookupLocation($this->campaign),
            new LookupFaction($this->campaign),
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        $abilityScore = fn () => $schema->integer()->min(1)->max(30);
        $nameDescription = fn () => $schema->object([
            'name' => $schema->string()->required()->description('Name of the trait, action, or ability'),
            'description' => $schema->string()->required()->description('Full description of what it does'),
        ])->required();

        return [
            // Narrative fields
            'name' => $schema->string()->required()
                ->description('Full name of the NPC'),
            'role' => $schema->string()->required()
                ->description('Role or occupation (e.g., Blacksmith, Quest Giver, Villain)'),
            'description' => $schema->string()->required()
                ->description('Physical appearance, background, and notable features'),
            'personality' => $schema->string()->required()
                ->description('Personality traits, temperament, and behavioral tendencies'),
            'motivation' => $schema->string()->required()
                ->description('What drives this NPC — their goals, fears, and desires'),
            'backstory' => $schema->string()->required()
                ->description('Brief backstory: past decisions, key events, and experiences that shaped this NPC'),
            'voice_description' => $schema->string()->required()
                ->description('How the NPC sounds: accent, pitch, cadence, vocal mannerisms'),
            'speech_patterns' => $schema->string()->required()
                ->description('How the NPC structures speech: formal, slang, repetitive, poetic, etc.'),
            'catchphrases' => $schema->array()->items(
                $schema->string()
            )->required()->description('2-4 memorable phrases the NPC frequently uses'),

            // Stat block identity
            'race' => $schema->string()->required()
                ->description('Race of the NPC (e.g., Human, Elf, Dwarf, Half-Orc, Tiefling)'),
            'size' => $schema->string()->enum(['Tiny', 'Small', 'Medium', 'Large', 'Huge', 'Gargantuan'])->required()
                ->description('Size category'),
            'alignment' => $schema->string()->required()
                ->description('Alignment (e.g., Lawful Good, Chaotic Neutral, True Neutral)'),

            // Combat stats
            'armor_class' => $schema->integer()->min(5)->max(30)->required()
                ->description('Armor Class value'),
            'armor_type' => $schema->string()
                ->description('Type of armor or defense (e.g., Natural armor, Leather armor, Shield)'),
            'hp_max' => $schema->integer()->min(1)->max(500)->required()
                ->description('Maximum hit points'),
            'hit_dice' => $schema->string()->required()
                ->description('Hit dice expression (e.g., 3d8+9, 10d10+20)'),
            'speed' => $schema->string()->required()
                ->description('Speed (e.g., "30 ft.", "30 ft., fly 60 ft.")'),
            'challenge_rating' => $schema->string()->required()
                ->description('Challenge Rating (e.g., "0", "1/4", "1/2", "1", "5", "20")'),

            // Ability scores
            'ability_scores' => $schema->object([
                'str' => $abilityScore()->required()->description('Strength score'),
                'dex' => $abilityScore()->required()->description('Dexterity score'),
                'con' => $abilityScore()->required()->description('Constitution score'),
                'int' => $abilityScore()->required()->description('Intelligence score'),
                'wis' => $abilityScore()->required()->description('Wisdom score'),
                'cha' => $abilityScore()->required()->description('Charisma score'),
            ])->required()->description('The six D&D ability scores'),

            // Proficiencies
            'saving_throw_proficiencies' => $schema->array()->items(
                $schema->string()->enum(['str', 'dex', 'con', 'int', 'wis', 'cha'])
            )->required()->description('Ability scores the NPC is proficient in for saving throws'),
            'skill_proficiencies' => $schema->array()->items(
                $schema->string()
            )->required()->description('Skill names the NPC is proficient in (e.g., athletics, stealth, perception)'),

            // Defenses
            'damage_resistances' => $schema->array()->items($schema->string())
                ->required()->description('Damage types the NPC is resistant to (empty array if none)'),
            'damage_immunities' => $schema->array()->items($schema->string())
                ->required()->description('Damage types the NPC is immune to (empty array if none)'),
            'condition_immunities' => $schema->array()->items($schema->string())
                ->required()->description('Conditions the NPC is immune to (empty array if none)'),

            // Senses and languages
            'senses' => $schema->string()->required()
                ->description('Senses (e.g., "Darkvision 60 ft., passive Perception 12")'),
            'languages' => $schema->string()->required()
                ->description('Languages known (e.g., "Common, Elvish")'),

            // Traits and actions
            'special_traits' => $schema->array()->items($nameDescription())
                ->required()->description('Passive special traits (e.g., Brave, Pack Tactics) — empty array if none'),
            'actions' => $schema->array()->items($nameDescription())
                ->required()->description('Actions the NPC can take in combat — include at least one attack'),
            'bonus_actions' => $schema->array()->items($nameDescription())
                ->required()->description('Bonus actions available — empty array if none'),
            'reactions' => $schema->array()->items($nameDescription())
                ->required()->description('Reactions available — empty array if none'),
            'legendary_actions' => $schema->array()->items($nameDescription())
                ->required()->description('Legendary actions — empty array if not a legendary creature'),

            // Optional spellcasting
            'spellcasting' => $schema->object([
                'ability' => $schema->string()->enum(['str', 'dex', 'con', 'int', 'wis', 'cha'])->required()
                    ->description('Spellcasting ability'),
                'spell_save_dc' => $schema->integer()->required()->description('Spell save DC'),
                'attack_bonus' => $schema->integer()->required()->description('Spell attack bonus'),
                'cantrips' => $schema->array()->items($schema->string())->required()
                    ->description('Cantrips known'),
                'spells_by_level' => $schema->object()->required()
                    ->description('Spells by level (keys: "1" through "9", values: arrays of spell names)'),
            ])->description('Spellcasting block — omit entirely if the NPC cannot cast spells'),

            // Campaign placement hints
            'suggested_faction' => $schema->string()
                ->description('Suggested faction name if relevant to campaign'),
            'suggested_location' => $schema->string()
                ->description('Suggested location name if relevant to campaign'),
        ];
    }
}
