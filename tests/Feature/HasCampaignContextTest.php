<?php

use App\Ai\Agents\SessionGenerator;
use App\Ai\Agents\SessionOutliner;
use App\Models\Campaign;
use App\Models\User;

test('session generator instructions include all five context fields', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create([
        'name' => 'Iron Realm',
        'premise' => 'The realm teeters on collapse.',
        'theme_tone' => 'Gritty political',
        'lore' => 'Ancient gods war in secret.',
        'world_rules' => 'No resurrection magic.',
    ]);

    $instructions = (new SessionGenerator($campaign))->instructions();

    expect($instructions)
        ->toContain('Iron Realm')
        ->toContain('The realm teeters on collapse.')
        ->toContain('Gritty political')
        ->toContain('Ancient gods war in secret.')
        ->toContain('No resurrection magic.');
});

test('session outliner instructions now include lore', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create([
        'name' => 'Shadow Campaign',
        'lore' => 'Forbidden lore text.',
    ]);

    $instructions = (new SessionOutliner($campaign))->instructions();

    expect($instructions)->toContain('Forbidden lore text.');
});

test('context omits null campaign fields', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create([
        'name' => 'Minimal Campaign',
        'premise' => null,
        'theme_tone' => null,
        'lore' => null,
        'world_rules' => null,
    ]);

    $instructions = (new SessionGenerator($campaign))->instructions();

    expect($instructions)
        ->toContain('Minimal Campaign')
        ->not->toContain('Premise:')
        ->not->toContain('Tone:')
        ->not->toContain('Lore:')
        ->not->toContain('World Rules:');
});
