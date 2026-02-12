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
class CampaignWizardAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        protected string $step,
        protected array $context = [],
    ) {}

    public function instructions(): Stringable|string
    {
        $contextLines = collect($this->context)
            ->filter()
            ->map(fn ($value, $key) => ucfirst(str_replace('_', ' ', $key)).": {$value}")
            ->implode("\n");

        return <<<PROMPT
You are a D&D campaign design assistant helping a Dungeon Master create a new campaign. Generate creative, detailed suggestions that fit the campaign's established context.

Current campaign context:
{$contextLines}

Generate suggestions appropriate for the "{$this->step}" step of campaign creation. Be creative but consistent with any previously established details.
PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return match ($this->step) {
            'world' => [
                'lore' => $schema->string()->required()
                    ->description('2-3 paragraphs of world lore and history'),
                'world_rules' => $schema->string()->required()
                    ->description('Special rules or unique aspects of this world'),
            ],
            'factions' => [
                'factions' => $schema->array()->items(
                    $schema->object([
                        'name' => $schema->string()->required(),
                        'description' => $schema->string()->required(),
                        'alignment' => $schema->string()->required(),
                        'goals' => $schema->string()->required(),
                    ])
                )->required()->description('3-4 factions that fit the campaign'),
            ],
            'locations' => [
                'locations' => $schema->array()->items(
                    $schema->object([
                        'name' => $schema->string()->required(),
                        'description' => $schema->string()->required(),
                        'region' => $schema->string()->required(),
                    ])
                )->required()->description('3-5 key locations for the campaign'),
            ],
            default => [
                'suggestion' => $schema->string()->required()
                    ->description('A creative suggestion for this step'),
            ],
        };
    }
}
