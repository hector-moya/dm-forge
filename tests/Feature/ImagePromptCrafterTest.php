<?php

use App\Ai\Agents\ImagePromptCrafter;
use Laravel\Ai\Contracts\HasStructuredOutput;

test('image prompt crafter implements correct interfaces', function () {
    $agent = new ImagePromptCrafter;

    expect($agent)->toBeInstanceOf(HasStructuredOutput::class);
    expect($agent->instructions())->toContain('DALL-E');
    expect($agent->instructions())->toContain('Dungeons & Dragons');
});

test('image prompt crafter instructions contain entity-specific guidelines', function () {
    $agent = new ImagePromptCrafter;
    $instructions = $agent->instructions();

    expect($instructions)->toContain('monster');
    expect($instructions)->toContain('npc');
    expect($instructions)->toContain('location');
    expect($instructions)->toContain('faction');
    expect($instructions)->toContain('scene');
    expect($instructions)->toContain('loot');
});

test('image prompt crafter returns structured output with prompt and orientation', function () {
    ImagePromptCrafter::fake([
        [
            'prompt' => 'A fearsome red dragon perched atop a volcanic mountain, wings spread wide, breathing fire into the stormy sky. Dark fantasy painting style with dramatic lighting.',
            'orientation' => 'landscape',
        ],
    ]);

    $response = (new ImagePromptCrafter)->prompt('Entity type: monster\n\nName: Red Dragon\nSize: Huge\nType: Dragon');

    expect($response['prompt'])->toContain('dragon');
    expect($response['orientation'])->toBe('landscape');

    ImagePromptCrafter::assertPrompted(fn ($prompt) => $prompt->contains('monster'));
});

test('image prompt crafter returns portrait orientation for npcs', function () {
    ImagePromptCrafter::fake([
        [
            'prompt' => 'A weathered half-orc tavern keeper with a warm smile, wearing a stained apron. Waist-up portrait in dark fantasy style.',
            'orientation' => 'portrait',
        ],
    ]);

    $response = (new ImagePromptCrafter)->prompt('Entity type: npc\n\nName: Gornik\nRole: Tavern Owner');

    expect($response['orientation'])->toBe('portrait');
});

test('image prompt crafter returns square orientation for items', function () {
    ImagePromptCrafter::fake([
        [
            'prompt' => 'A gleaming enchanted longsword with a sapphire-encrusted hilt, radiating blue magical energy. Item showcase on dark velvet.',
            'orientation' => 'square',
        ],
    ]);

    $response = (new ImagePromptCrafter)->prompt('Entity type: loot\n\nName: Frostbrand\nRarity: Rare');

    expect($response['orientation'])->toBe('square');
});
