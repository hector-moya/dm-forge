<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class TagClassifier implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are a D&D action classifier. Given a description of a character's action during a game session, classify it with appropriate alignment and narrative tags.

Tag categories:
- alignment: Tags related to moral alignment (e.g., "mercy", "cruelty", "honor", "deception", "charity", "theft")
- narrative: Tags related to story elements (e.g., "combat", "diplomacy", "exploration", "puzzle", "roleplay")
- consequence: Tags related to potential outcomes (e.g., "alliance", "enemy-made", "reputation", "debt", "secret-revealed")

Return 2-5 tags total, selecting the most relevant ones. Each tag should be a short lowercase hyphenated label.
PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'tags' => $schema->array(
                $schema->object([
                    'label' => $schema->string()->required()->description('Short hyphenated tag label'),
                    'category' => $schema->string()->enum(['alignment', 'narrative', 'consequence'])->required(),
                ])
            )->required()->description('Array of classified tags for the action'),
        ];
    }
}
