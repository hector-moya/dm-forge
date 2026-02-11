<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetCharacterSheet;
use App\Ai\Tools\LookupLocation;
use App\Ai\Tools\LookupNpc;
use App\Models\Campaign;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

class SessionOutliner implements Agent, HasTools, HasStructuredOutput
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
        if ($this->campaign->world_rules) {
            $context .= "\nWorld Rules: {$this->campaign->world_rules}";
        }

        return <<<PROMPT
You are an expert D&D session planner. Given a session premise or hook, generate a complete session outline with scenes, encounters, and branch options.

Campaign Context:
{$context}

Guidelines:
- Create 3-5 scenes that form a coherent narrative arc
- Each scene should have a clear purpose and description
- Include 1-3 encounters where appropriate (not every scene needs combat)
- Add 1-2 branch options at key decision points
- Encounters should have appropriate difficulty ratings
- Branch options should present meaningful choices with different consequences
- Use the campaign's NPCs, locations, and lore when relevant (use tools to look them up)

Use the available tools to look up NPCs, locations, and characters to make the session feel connected to the campaign world.
PROMPT;
    }

    public function tools(): iterable
    {
        return [
            new LookupNpc($this->campaign),
            new LookupLocation($this->campaign),
            new GetCharacterSheet($this->campaign),
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'scenes' => $schema->array()->items(
                $schema->object([
                    'title' => $schema->string()->required(),
                    'description' => $schema->string()->required(),
                    'notes' => $schema->string()->description('DM-only notes for running this scene'),
                    'encounters' => $schema->array()->items(
                        $schema->object([
                            'name' => $schema->string()->required(),
                            'description' => $schema->string(),
                            'environment' => $schema->string(),
                            'difficulty' => $schema->string()->enum(['easy', 'medium', 'hard', 'deadly'])->required(),
                            'monsters' => $schema->array()->items(
                                $schema->object([
                                    'name' => $schema->string()->required(),
                                    'hp_max' => $schema->integer()->min(1)->required(),
                                    'armor_class' => $schema->integer()->min(1)->required(),
                                    'count' => $schema->integer()->min(1)->required(),
                                ])
                            ),
                        ])
                    ),
                    'branch_options' => $schema->array()->items(
                        $schema->object([
                            'label' => $schema->string()->required(),
                            'description' => $schema->string()->required(),
                            'consequences' => $schema->array()->items(
                                $schema->object([
                                    'type' => $schema->string()->enum(['immediate', 'delayed', 'meta'])->required(),
                                    'description' => $schema->string()->required(),
                                ])
                            ),
                        ])
                    ),
                ])
            )->required()->description('Ordered list of scenes forming the session'),
        ];
    }
}
