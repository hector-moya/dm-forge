<?php

namespace App\Ai\Agents;

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
You are a creative D&D NPC designer. Generate a detailed, memorable NPC that fits naturally into the campaign world.

{$context}

Guidelines:
- Create NPCs with distinct personalities, clear motivations, and memorable quirks
- Voice descriptions should help the DM roleplay the NPC (accent, cadence, pitch, mannerisms)
- Speech patterns should describe HOW the NPC talks (formal, slang, repetitive, poetic, etc.)
- Catchphrases should be 2-4 short, memorable phrases the NPC frequently uses
- Use the lookup tools to reference existing NPCs and locations when relevant
- The NPC should feel organic within the campaign's theme and tone
- Suggest a faction and location if relevant to the campaign
PROMPT;
    }

    public function tools(): iterable
    {
        return [
            new LookupNpc($this->campaign),
            new LookupLocation($this->campaign),
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
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
            'voice_description' => $schema->string()->required()
                ->description('How the NPC sounds: accent, pitch, cadence, vocal mannerisms'),
            'speech_patterns' => $schema->string()->required()
                ->description('How the NPC structures speech: formal, slang, repetitive, poetic, etc.'),
            'catchphrases' => $schema->array()->items(
                $schema->string()
            )->required()->description('2-4 memorable phrases the NPC frequently uses'),
            'suggested_faction' => $schema->string()
                ->description('Suggested faction name if relevant to campaign'),
            'suggested_location' => $schema->string()
                ->description('Suggested location name if relevant to campaign'),
        ];
    }
}
