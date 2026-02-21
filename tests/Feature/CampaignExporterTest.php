<?php

use App\Models\Campaign;
use App\Models\Character;
use App\Models\Faction;
use App\Models\GameSession;
use App\Models\Location;
use App\Models\Lore;
use App\Models\Npc;
use App\Models\SpecialMechanic;
use App\Models\User;
use App\Models\WorldRule;
use App\Services\CampaignExporter;

test('toMarkdown includes campaign name as h1', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create(['name' => 'Ashes of Ember']);

    $result = (new CampaignExporter)->toMarkdown($campaign);

    expect($result)->toContain('# Ashes of Ember');
});

test('toMarkdown includes premise and lore when present', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create([
        'premise' => 'A realm at war.',
        'lore' => 'The ancient dragons slumber.',
    ]);

    $result = (new CampaignExporter)->toMarkdown($campaign);

    expect($result)
        ->toContain('## Premise')
        ->toContain('A realm at war.')
        ->toContain('## Lore')
        ->toContain('The ancient dragons slumber.');
});

test('toMarkdown omits sections when collections are empty', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    $result = (new CampaignExporter)->toMarkdown($campaign);

    expect($result)
        ->not->toContain('## Factions')
        ->not->toContain('## NPCs')
        ->not->toContain('## Characters')
        ->not->toContain('## Locations')
        ->not->toContain('## Sessions');
});

test('toMarkdown renders factions section with alignment and goals', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    Faction::factory()->for($campaign)->create([
        'name' => 'The Iron Circle',
        'alignment' => 'Lawful Evil',
        'goals' => 'Dominate the trade routes.',
    ]);

    $result = (new CampaignExporter)->toMarkdown($campaign);

    expect($result)
        ->toContain('## Factions')
        ->toContain('### The Iron Circle')
        ->toContain('**Alignment:** Lawful Evil')
        ->toContain('**Goals:** Dominate the trade routes.');
});

test('toMarkdown renders dead npc with dead status tag', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    Npc::factory()->for($campaign)->create(['name' => 'Mira', 'is_alive' => false]);

    $result = (new CampaignExporter)->toMarkdown($campaign);

    expect($result)->toContain('[Dead]');
});

test('toMarkdown renders locations with region', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    Location::factory()->for($campaign)->create([
        'name' => 'Thornvale',
        'region' => 'Northern Reaches',
        'description' => 'A frost-bitten town.',
    ]);

    $result = (new CampaignExporter)->toMarkdown($campaign);

    expect($result)
        ->toContain('## Locations')
        ->toContain('### Thornvale (Northern Reaches)')
        ->toContain('A frost-bitten town.');
});

test('toMarkdown renders character stats', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    Character::factory()->for($campaign)->create([
        'name' => 'Aldric',
        'player_name' => 'John',
        'class' => 'Fighter',
        'level' => 5,
    ]);

    $result = (new CampaignExporter)->toMarkdown($campaign);

    expect($result)
        ->toContain('### Aldric (Player: John)')
        ->toContain('**Class:** Fighter')
        ->toContain('**Level:** 5');
});

test('toMarkdown sorts sessions by session number', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    GameSession::factory()->for($campaign)->create(['session_number' => 3, 'title' => 'Third']);
    GameSession::factory()->for($campaign)->create(['session_number' => 1, 'title' => 'First']);

    $result = (new CampaignExporter)->toMarkdown($campaign);

    expect(strpos($result, 'Session #1'))->toBeLessThan(strpos($result, 'Session #3'));
});

test('toMarkdown renders lore entries from relationship', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $lore = Lore::factory()->for($user)->create([
        'name' => 'The Sundering',
        'description' => 'A cataclysmic event that split the world.',
    ]);
    $campaign->lores()->attach($lore->id);

    $result = (new CampaignExporter)->toMarkdown($campaign);

    expect($result)
        ->toContain('## Lore')
        ->toContain('### The Sundering')
        ->toContain('A cataclysmic event that split the world.');
});

test('toMarkdown renders world rules and special mechanics from relationships', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    $rule = WorldRule::factory()->for($user)->create([
        'name' => 'No Resurrection',
        'description' => 'Death is permanent in this world.',
    ]);
    $campaign->worldRules()->attach($rule->id);

    $mechanic = SpecialMechanic::factory()->for($user)->create([
        'name' => 'Corruption System',
        'description' => 'Players accumulate corruption points over time.',
    ]);
    $campaign->specialMechanics()->attach($mechanic->id);

    $result = (new CampaignExporter)->toMarkdown($campaign);

    expect($result)
        ->toContain('## World Rules')
        ->toContain('### No Resurrection')
        ->toContain('Death is permanent in this world.')
        ->toContain('## Special Mechanics')
        ->toContain('### Corruption System')
        ->toContain('Players accumulate corruption points over time.');
});
