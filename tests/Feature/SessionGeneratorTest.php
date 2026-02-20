<?php

use App\Ai\Agents\SessionGenerator;
use App\Ai\Tools\LookupSrdMonster;
use App\Models\Campaign;
use App\Models\SrdMonster;
use App\Models\User;
use Laravel\Ai\Tools\Request;
use Livewire\Livewire;

test('session generator agent implements correct interfaces', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    $agent = new SessionGenerator($campaign);

    expect($agent)->toBeInstanceOf(\Laravel\Ai\Contracts\HasStructuredOutput::class);
    expect($agent)->toBeInstanceOf(\Laravel\Ai\Contracts\HasTools::class);
    expect($agent->instructions())->toContain('D&D session designer');
});

test('session generator instructions include campaign context', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create([
        'name' => 'Dragon Age',
        'premise' => 'Dragons return to the realm',
        'theme_tone' => 'Dark fantasy',
    ]);

    $agent = new SessionGenerator($campaign);
    $instructions = $agent->instructions();

    expect($instructions)->toContain('Dragon Age');
    expect($instructions)->toContain('Dragons return to the realm');
    expect($instructions)->toContain('Dark fantasy');
});

test('lookup srd monster tool returns matching monsters', function () {
    SrdMonster::create([
        'index' => 'goblin',
        'name' => 'Goblin',
        'size' => 'Small',
        'type' => 'humanoid',
        'alignment' => 'neutral evil',
        'armor_class' => 15,
        'hit_points' => 7,
        'hit_dice' => '2d6',
        'challenge_rating' => 0.25,
        'xp' => 50,
    ]);

    $tool = new LookupSrdMonster;
    $result = $tool->handle(new Request(['name' => 'Goblin']));

    expect($result)->toContain('Goblin');
    expect($result)->toContain('CR: 0.25');
    expect($result)->toContain('HP: 7');
    expect($result)->toContain('AC: 15');
});

test('lookup srd monster tool filters by type', function () {
    SrdMonster::create([
        'index' => 'zombie',
        'name' => 'Zombie',
        'size' => 'Medium',
        'type' => 'undead',
        'alignment' => 'neutral evil',
        'armor_class' => 8,
        'hit_points' => 22,
        'hit_dice' => '3d8+9',
        'challenge_rating' => 0.25,
        'xp' => 50,
    ]);

    SrdMonster::create([
        'index' => 'wolf',
        'name' => 'Wolf',
        'size' => 'Medium',
        'type' => 'beast',
        'alignment' => 'unaligned',
        'armor_class' => 13,
        'hit_points' => 11,
        'hit_dice' => '2d8+2',
        'challenge_rating' => 0.25,
        'xp' => 50,
    ]);

    $tool = new LookupSrdMonster;
    $result = $tool->handle(new Request(['type' => 'undead']));

    expect($result)->toContain('Zombie');
    expect($result)->not->toContain('Wolf');
});

test('lookup srd monster tool returns empty message when no matches', function () {
    $tool = new LookupSrdMonster;
    $result = $tool->handle(new Request(['name' => 'NonExistentMonster']));

    expect($result)->toContain('No SRD monsters found');
});

test('session generator creates full session with scenes encounters and monsters', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    SrdMonster::create([
        'index' => 'skeleton',
        'name' => 'Skeleton',
        'size' => 'Medium',
        'type' => 'undead',
        'alignment' => 'lawful evil',
        'armor_class' => 13,
        'hit_points' => 13,
        'hit_dice' => '2d8+4',
        'challenge_rating' => 0.25,
        'xp' => 50,
    ]);

    SessionGenerator::fake([
        [
            'title' => 'The Crypt of Shadows',
            'setup_text' => 'The air grows cold as you descend into the ancient crypt.',
            'dm_notes' => 'The lich is watching from the shadows.',
            'scenes' => [
                [
                    'title' => 'The Entrance',
                    'description' => 'A crumbling stone doorway leads into darkness.',
                    'notes' => 'The door is trapped with a poison dart.',
                    'encounters' => [
                        [
                            'name' => 'Skeleton Guards',
                            'description' => 'Skeletal warriors rise from alcoves.',
                            'environment' => 'Narrow stone corridor',
                            'difficulty' => 'easy',
                            'monsters' => [
                                ['name' => 'Skeleton', 'quantity' => 3],
                            ],
                        ],
                    ],
                    'branch_options' => [
                        [
                            'label' => 'Sneak past',
                            'description' => 'Attempt to sneak past the skeletons undetected.',
                        ],
                        [
                            'label' => 'Fight through',
                            'description' => 'Engage the skeletons in combat.',
                        ],
                    ],
                    'puzzle' => [
                        'name' => 'The Runic Door',
                        'description' => 'Ancient runes glow on a sealed door.',
                        'solution' => 'Speak the runes backward.',
                        'hint_tier_1' => 'The runes seem to shimmer when read aloud.',
                        'hint_tier_2' => 'A mirror on the wall reflects the runes in reverse.',
                        'hint_tier_3' => 'Try reading them right to left.',
                        'difficulty' => 'medium',
                        'puzzle_type' => 'cipher',
                    ],
                ],
                [
                    'title' => 'The Inner Sanctum',
                    'description' => 'A vast chamber filled with sarcophagi.',
                    'notes' => 'The boss encounter. DM should adjust difficulty based on party resources.',
                    'encounters' => [],
                    'branch_options' => [],
                    'puzzle' => null,
                ],
            ],
        ],
    ]);

    Livewire::actingAs($user)
        ->test('pages::sessions.builder', ['campaign' => $campaign])
        ->set('generateContext', 'An undead dungeon crawl')
        ->call('generateSession')
        ->assertRedirect();

    SessionGenerator::assertPrompted(fn ($prompt) => $prompt->contains('undead dungeon crawl'));

    // Verify session created
    $session = $campaign->gameSessions()->where('title', 'The Crypt of Shadows')->first();
    expect($session)->not->toBeNull();
    expect($session->setup_text)->toContain('ancient crypt');
    expect($session->dm_notes)->toContain('lich');

    // Verify scenes
    $scenes = $session->scenes()->orderBy('sort_order')->get();
    expect($scenes)->toHaveCount(2);
    expect($scenes[0]->title)->toBe('The Entrance');
    expect($scenes[1]->title)->toBe('The Inner Sanctum');

    // Verify encounters and monsters
    $encounters = $scenes[0]->encounters;
    expect($encounters)->toHaveCount(1);
    expect($encounters[0]->name)->toBe('Skeleton Guards');
    expect($encounters[0]->difficulty)->toBe('easy');

    $monsters = $encounters[0]->monsters;
    expect($monsters)->toHaveCount(3);
    expect($monsters[0]->name)->toBe('Skeleton');
    expect($monsters[0]->srd_monster_id)->not->toBeNull();
    expect($monsters[0]->hp_max)->toBe(13);
    expect($monsters[0]->armor_class)->toBe(13);

    // Verify branch options
    $branches = $scenes[0]->branchOptions;
    expect($branches)->toHaveCount(2);
    expect($branches[0]->label)->toBe('Sneak past');

    // Verify puzzle
    $puzzle = $campaign->puzzles()->where('scene_id', $scenes[0]->id)->first();
    expect($puzzle)->not->toBeNull();
    expect($puzzle->name)->toBe('The Runic Door');
    expect($puzzle->difficulty)->toBe('medium');
    expect($puzzle->puzzle_type)->toBe('cipher');
    expect($puzzle->hint_tier_1)->toContain('shimmer');
});

test('session generator appends scenes to existing session', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = \App\Models\GameSession::factory()->for($campaign)->create();

    // Add an existing scene
    $session->scenes()->create([
        'title' => 'Existing Scene',
        'sort_order' => 1,
    ]);

    SessionGenerator::fake([
        [
            'title' => 'Updated Title',
            'setup_text' => 'New setup text',
            'dm_notes' => 'New DM notes',
            'scenes' => [
                [
                    'title' => 'New AI Scene',
                    'description' => 'A freshly generated scene.',
                    'notes' => 'AI notes',
                    'encounters' => [],
                    'branch_options' => [],
                    'puzzle' => null,
                ],
            ],
        ],
    ]);

    Livewire::actingAs($user)
        ->test('pages::sessions.builder', ['session' => $session])
        ->call('generateSession')
        ->assertRedirect();

    // Verify existing scene preserved, new scene appended
    $scenes = $session->fresh()->scenes()->orderBy('sort_order')->get();
    expect($scenes)->toHaveCount(2);
    expect($scenes[0]->title)->toBe('Existing Scene');
    expect($scenes[0]->sort_order)->toBe(1);
    expect($scenes[1]->title)->toBe('New AI Scene');
    expect($scenes[1]->sort_order)->toBe(2);
});

test('session generator handles ai failure gracefully', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    SessionGenerator::fake(function () {
        throw new \RuntimeException('API rate limit exceeded');
    });

    Livewire::actingAs($user)
        ->test('pages::sessions.builder', ['campaign' => $campaign])
        ->call('openGenerateModal')
        ->assertSet('showGenerateModal', true)
        ->call('generateSession')
        ->assertSet('generating', false);

    // No session should have been created
    expect($campaign->gameSessions()->count())->toBe(0);
});

test('session generator creates monsters without srd match', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    SessionGenerator::fake([
        [
            'title' => 'Custom Monster Session',
            'setup_text' => 'Setup',
            'dm_notes' => 'Notes',
            'scenes' => [
                [
                    'title' => 'Boss Fight',
                    'description' => 'A custom boss encounter.',
                    'notes' => 'Custom monster',
                    'encounters' => [
                        [
                            'name' => 'Boss Battle',
                            'description' => 'Fight the custom boss.',
                            'environment' => 'Throne room',
                            'difficulty' => 'hard',
                            'monsters' => [
                                ['name' => 'Shadow Wyrm', 'quantity' => 1],
                            ],
                        ],
                    ],
                    'branch_options' => [],
                    'puzzle' => null,
                ],
            ],
        ],
    ]);

    Livewire::actingAs($user)
        ->test('pages::sessions.builder', ['campaign' => $campaign])
        ->call('generateSession')
        ->assertRedirect();

    $session = $campaign->gameSessions()->first();
    $monster = $session->scenes->first()->encounters->first()->monsters->first();

    expect($monster->name)->toBe('Shadow Wyrm');
    expect($monster->srd_monster_id)->toBeNull();
    expect($monster->hp_max)->toBe(1); // Default when no SRD match
    expect($monster->armor_class)->toBe(10); // Default when no SRD match
});
