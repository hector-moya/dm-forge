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
class LocationGenerator implements Agent, HasStructuredOutput, HasTools
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
You are a creative D&D world-builder specializing in location design. Generate a detailed, immersive location that fits naturally into the campaign world.

{$context}

Guidelines:
- Create locations with vivid sensory details (sights, sounds, smells, atmosphere)
- Include a brief history of how the location developed over time
- Notable features should be things players can interact with or investigate
- Use the lookup tools to reference existing locations, NPCs, and factions when relevant
- The location should feel organic within the campaign's theme and tone
- Suggest a parent location if it makes sense as a sub-area (e.g., a tavern within a city)
- Suggest a controlling or associated faction if relevant
PROMPT;
    }

    public function tools(): iterable
    {
        return [
            new LookupLocation($this->campaign),
            new LookupNpc($this->campaign),
            new LookupFaction($this->campaign),
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->required()
                ->description('Name of the location'),
            'description' => $schema->string()->required()
                ->description('Detailed physical description with sensory details and atmosphere'),
            'region' => $schema->string()->required()
                ->description('Geographical region or area this location belongs to'),
            'history' => $schema->string()->required()
                ->description('Brief history of how this location developed and what shaped it'),
            'notable_features' => $schema->array()->items(
                $schema->string()
            )->required()->description('2-4 notable features, landmarks, or points of interest'),
            'suggested_parent_location' => $schema->string()
                ->description('Name of an existing parent location if this is a sub-area'),
            'suggested_faction' => $schema->string()
                ->description('Name of a controlling or associated faction if relevant'),
        ];
    }
}
