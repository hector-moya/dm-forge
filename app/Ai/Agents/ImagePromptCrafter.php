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
#[Temperature(0.7)]
class ImagePromptCrafter implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are an expert at crafting image generation prompts for DALL-E. You specialize in Dungeons & Dragons fantasy art.

Given an entity type and its details, create a vivid, detailed prompt that will produce high-quality fantasy artwork.

## Style Guidelines
- Use a consistent dark fantasy / high fantasy art style
- Rich lighting, dramatic atmosphere, painterly quality
- Never include text, words, letters, or watermarks in the image
- No UI elements, borders, or frames

## Entity-Specific Guidelines

**monster**: Dramatic action pose or lurking in natural habitat. Emphasize the creature's most dangerous or distinctive features. Dark, atmospheric lighting. Show scale relative to environment.

**npc**: Character portrait, waist-up or full body. Expressive face showing personality. Clothing and accessories that reflect their role/occupation. Background hints at their usual environment.

**location**: Wide landscape or environmental vista. Atmospheric mood matching the location's nature (mysterious, grand, eerie, welcoming). Rich environmental details. Cinematic composition.

**faction**: Group banner, emblem, or a scene showing faction members in their element. Colors and symbols that represent their identity and goals. Heraldic or organizational feel.

**scene**: Wide cinematic composition showing the environment and mood. Dramatic lighting that sets the tone (tense, mysterious, triumphant). Should evoke the feeling of the scene without showing specific characters.

**loot**: Item showcase on a neutral or thematic background. Detailed craftsmanship visible. Magical glow or enchantment effects if magical. Show the item's scale and materials clearly.

## Output
Produce a single, detailed prompt string (2-4 sentences) and choose the best orientation for the entity type.
PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'prompt' => $schema->string()->required()
                ->description('The DALL-E image generation prompt. 2-4 sentences of vivid, detailed description.'),
            'orientation' => $schema->string()->enum(['square', 'portrait', 'landscape'])->required()
                ->description('Best orientation: portrait for characters/NPCs, landscape for locations/scenes, square for items/monsters/factions'),
        ];
    }
}
