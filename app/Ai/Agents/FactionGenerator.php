<?php

namespace App\Ai\Agents;

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
class FactionGenerator implements Agent, HasStructuredOutput, HasTools
{
    use Promptable;

    public function __construct(
        protected Campaign $campaign,
    ) {}

    public function instructions(): Stringable|string
    {
        $context = "Campaign: {$this->campaign->name}";
        if ($this->campaign->premise) {
            $context .= "\nPremise: {$this->campaign->premise}";
        }
        if ($this->campaign->theme_tone) {
            $context .= "\nTone: {$this->campaign->theme_tone}";
        }
        if ($this->campaign->lore) {
            $context .= "\nLore: {$this->campaign->lore}";
        }

        return <<<PROMPT
You are a creative D&D world-builder specializing in faction design. Generate a detailed, compelling faction that fits naturally into the campaign world.

{$context}

Guidelines:
- Create factions with clear structure, reputation, and political dynamics
- Goals should include both short-term objectives and long-term ambitions
- Resources should describe assets, territory, influence, and manpower
- Relationships should describe how this faction views other existing factions
- Use the lookup tools to reference existing factions, NPCs, and locations when relevant
- The faction should feel organic within the campaign's theme and tone
- Suggest a leader NPC name and headquarters location if relevant
- Alignment should reflect the faction's overall moral and ethical stance
PROMPT;
    }

    public function tools(): iterable
    {
        return [
            new LookupFaction($this->campaign),
            new LookupNpc($this->campaign),
            new LookupLocation($this->campaign),
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->required()
                ->description('Name of the faction'),
            'description' => $schema->string()->required()
                ->description('Overview of the faction: structure, reputation, and role in the world'),
            'alignment' => $schema->string()->required()
                ->description('D&D alignment (e.g., Lawful Good, Chaotic Evil, True Neutral)'),
            'goals' => $schema->string()->required()
                ->description('Current objectives and long-term ambitions'),
            'resources' => $schema->string()->required()
                ->description('Assets, territory, influence, and manpower'),
            'relationships' => $schema->array()->items(
                $schema->object([
                    'faction_name' => $schema->string()->required()
                        ->description('Name of the related faction'),
                    'attitude' => $schema->string()->required()
                        ->enum(['ally', 'rival', 'neutral', 'enemy'])
                        ->description('How this faction views the other'),
                    'reason' => $schema->string()->required()
                        ->description('Why this relationship exists'),
                ])
            )->description('Relationships with other existing factions'),
            'suggested_leader' => $schema->string()
                ->description('Suggested NPC name for the faction leader'),
            'suggested_headquarters' => $schema->string()
                ->description('Suggested location name for faction headquarters'),
        ];
    }
}
