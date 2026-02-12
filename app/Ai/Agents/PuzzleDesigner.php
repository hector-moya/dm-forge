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
class PuzzleDesigner implements Agent, HasStructuredOutput, HasTools
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
You are a creative D&D puzzle designer. Generate engaging, solvable puzzles that fit naturally into the campaign world.

{$context}

Guidelines:
- Create puzzles that are fun, challenging, and thematic to the campaign setting
- The solution should be logical and fair — players should be able to solve it with the clues given
- Hints should be tiered: tier 1 is a subtle nudge, tier 2 is a clearer clue, tier 3 nearly gives away the answer
- Riddles should have poetic or rhyming elements when appropriate
- Logic puzzles should have clear rules and a deterministic solution
- Physical puzzles should describe mechanisms the DM can narrate
- Cipher puzzles should include the encoding method in the solution
- Pattern puzzles should have a clear repeating or mathematical basis
- Use the lookup tools to reference existing NPCs and locations for thematic integration
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
                ->description('A short, evocative name for the puzzle'),
            'description' => $schema->string()->required()
                ->description('Full description of the puzzle as the DM would present it to players, including any visual or environmental details'),
            'solution' => $schema->string()->required()
                ->description('The complete solution and explanation of how it works'),
            'hint_tier_1' => $schema->string()->required()
                ->description('A subtle hint — a gentle nudge in the right direction'),
            'hint_tier_2' => $schema->string()->required()
                ->description('A clearer hint — narrows down the approach significantly'),
            'hint_tier_3' => $schema->string()->required()
                ->description('A strong hint — nearly gives away the answer without stating it directly'),
        ];
    }
}
