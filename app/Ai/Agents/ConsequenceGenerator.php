<?php

namespace App\Ai\Agents;

use App\Ai\Tools\LookupNpc;
use App\Models\Campaign;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

class ConsequenceGenerator implements Agent, HasTools, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        protected Campaign $campaign,
    ) {}

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are a D&D consequence designer. Given a branch option (a choice the party might make), generate realistic and interesting consequences across three timeframes:

- **Immediate**: What happens right away as a direct result of this choice
- **Delayed**: What happens later in the session or in future sessions as a ripple effect
- **Meta**: How this affects the broader campaign world, faction relationships, or narrative themes

Guidelines:
- Generate 1-2 consequences per type (3-6 total)
- Consequences should feel natural and logical
- Include both positive and negative potential outcomes
- Reference campaign NPCs and factions when relevant (use tools to look them up)
- Delayed consequences should create future plot hooks
- Meta consequences should affect world state in meaningful ways
PROMPT;
    }

    public function tools(): iterable
    {
        return [
            new LookupNpc($this->campaign),
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'consequences' => $schema->array(
                $schema->object([
                    'type' => $schema->string()->enum(['immediate', 'delayed', 'meta'])->required(),
                    'description' => $schema->string()->required()
                        ->description('Description of the consequence'),
                ])
            )->required()->description('Array of consequences across all three timeframes'),
        ];
    }
}
