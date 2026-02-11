<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetCharacterSheet;
use App\Models\Campaign;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

class AlignmentAdvisor implements Agent, HasStructuredOutput, HasTools
{
    use Promptable;

    public function __construct(
        protected Campaign $campaign,
    ) {}

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are an expert D&D alignment advisor. Given a character action description, analyze it through the lens of the D&D alignment system and suggest appropriate Good/Evil and Law/Chaos score adjustments.

Guidelines for scoring:
- Scores range from -5 (strong shift) to +5 (strong shift)
- Good/Evil axis: positive = Good, negative = Evil
  - Helping innocents, self-sacrifice, charity = positive
  - Cruelty, selfishness, causing suffering = negative
- Law/Chaos axis: positive = Lawful, negative = Chaotic
  - Following rules, honoring agreements, discipline = positive
  - Breaking laws, acting on impulse, deception = negative
- Minor actions should have small deltas (1-2)
- Major actions should have larger deltas (3-5)
- Zero means the action is neutral on that axis

Use the GetCharacterSheet tool to understand the character's current state and history before making your recommendation.

Provide a brief reasoning explaining your suggested adjustments.
PROMPT;
    }

    public function tools(): iterable
    {
        return [
            new GetCharacterSheet($this->campaign),
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'good_evil_delta' => $schema->integer()->min(-5)->max(5)->required()
                ->description('Suggested Good/Evil score change. Positive = Good, Negative = Evil.'),
            'law_chaos_delta' => $schema->integer()->min(-5)->max(5)->required()
                ->description('Suggested Law/Chaos score change. Positive = Lawful, Negative = Chaotic.'),
            'reasoning' => $schema->string()->required()
                ->description('Brief explanation of why these alignment shifts are appropriate.'),
            'tags' => $schema->array()->items($schema->string())->required()
                ->description('Alignment-relevant tags for this action (e.g., "mercy", "theft", "honor", "deception").'),
        ];
    }
}
