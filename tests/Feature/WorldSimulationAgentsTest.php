<?php

use App\Ai\Agents\FactionGenerator;
use App\Ai\Agents\LocationGenerator;
use App\Ai\Agents\NpcGenerator;
use App\Ai\Tools\LookupFaction;
use App\Models\Campaign;
use App\Models\Faction;
use App\Models\Location;
use App\Models\Npc;
use App\Models\User;
use Laravel\Ai\Tools\Request;

// ── Location Generator ────────────────────────────────────────────────

test('location generator agent implements correct interfaces', function () {
    $campaign = Campaign::factory()->for(User::factory())->create();
    $agent = new LocationGenerator($campaign);

    expect($agent)->toBeInstanceOf(\Laravel\Ai\Contracts\HasStructuredOutput::class);
    expect($agent)->toBeInstanceOf(\Laravel\Ai\Contracts\HasTools::class);
    expect($agent->instructions())->toContain('location design');
});

test('location generator includes campaign context in instructions', function () {
    $campaign = Campaign::factory()->for(User::factory())->create([
        'name' => 'Dragon Rising',
        'premise' => 'Dragons return to the realm',
        'theme_tone' => 'Epic fantasy',
    ]);

    $agent = new LocationGenerator($campaign);
    $instructions = $agent->instructions();

    expect($instructions)->toContain('Dragon Rising');
    expect($instructions)->toContain('Dragons return to the realm');
    expect($instructions)->toContain('Epic fantasy');
});

test('location generator returns structured output', function () {
    $campaign = Campaign::factory()->for(User::factory())->create();

    LocationGenerator::fake([
        [
            'name' => 'The Whispering Woods',
            'description' => 'A dense forest where the trees seem to murmur',
            'region' => 'Northern Marches',
            'history' => 'Once home to an ancient elven civilization',
            'notable_features' => ['Ancient stone circle', 'Bioluminescent mushrooms'],
            'suggested_parent_location' => null,
            'suggested_faction' => 'Forest Wardens',
        ],
    ]);

    $response = (new LocationGenerator($campaign))->prompt('Generate a forest location');

    expect($response['name'])->toBe('The Whispering Woods');
    expect($response['region'])->toBe('Northern Marches');
    expect($response['notable_features'])->toHaveCount(2);

    LocationGenerator::assertPrompted(fn ($prompt) => $prompt->contains('forest'));
});

// ── Faction Generator ─────────────────────────────────────────────────

test('faction generator agent implements correct interfaces', function () {
    $campaign = Campaign::factory()->for(User::factory())->create();
    $agent = new FactionGenerator($campaign);

    expect($agent)->toBeInstanceOf(\Laravel\Ai\Contracts\HasStructuredOutput::class);
    expect($agent)->toBeInstanceOf(\Laravel\Ai\Contracts\HasTools::class);
    expect($agent->instructions())->toContain('faction design');
});

test('faction generator includes campaign context in instructions', function () {
    $campaign = Campaign::factory()->for(User::factory())->create([
        'name' => 'Shadow War',
        'premise' => 'A cold war between two empires',
        'theme_tone' => 'Political intrigue',
    ]);

    $agent = new FactionGenerator($campaign);
    $instructions = $agent->instructions();

    expect($instructions)->toContain('Shadow War');
    expect($instructions)->toContain('cold war between two empires');
    expect($instructions)->toContain('Political intrigue');
});

test('faction generator returns structured output', function () {
    $campaign = Campaign::factory()->for(User::factory())->create();

    FactionGenerator::fake([
        [
            'name' => 'The Iron Brotherhood',
            'description' => 'A militant order of blacksmiths and warriors',
            'alignment' => 'Lawful Neutral',
            'goals' => 'Control the iron trade routes',
            'resources' => 'Extensive forges, trained militia, trade agreements',
            'relationships' => [
                ['faction_name' => 'Merchants Guild', 'attitude' => 'rival', 'reason' => 'Competing trade interests'],
            ],
            'suggested_leader' => 'Commander Bronzebeard',
            'suggested_headquarters' => 'The Foundry',
        ],
    ]);

    $response = (new FactionGenerator($campaign))->prompt('Generate a warrior faction');

    expect($response['name'])->toBe('The Iron Brotherhood');
    expect($response['alignment'])->toBe('Lawful Neutral');
    expect($response['relationships'])->toHaveCount(1);

    FactionGenerator::assertPrompted(fn ($prompt) => $prompt->contains('warrior'));
});

// ── Enhanced NPC Generator ────────────────────────────────────────────

test('npc generator includes backstory in schema', function () {
    $campaign = Campaign::factory()->for(User::factory())->create();

    NpcGenerator::fake([
        [
            'name' => 'Aria Moonwhisper',
            'role' => 'Scholar',
            'description' => 'A tall elf with silver hair',
            'personality' => 'Curious and reserved',
            'motivation' => 'Uncover forgotten lore',
            'voice_description' => 'Soft and melodic',
            'speech_patterns' => 'Formal, ancient phrasing',
            'catchphrases' => ['Knowledge is the only true currency'],
            'backstory' => 'Exiled from her homeland for forbidden research',
            'suggested_faction' => 'Lorekeeper Circle',
            'suggested_location' => 'The Great Library',
        ],
    ]);

    $response = (new NpcGenerator($campaign))->prompt('Generate a scholarly NPC');

    expect($response['backstory'])->toBe('Exiled from her homeland for forbidden research');
    expect($response['name'])->toBe('Aria Moonwhisper');
});

test('npc generator instructions reference faction dynamics', function () {
    $campaign = Campaign::factory()->for(User::factory())->create();
    $agent = new NpcGenerator($campaign);

    expect($agent->instructions())->toContain('faction');
});

// ── LookupFaction Tool ────────────────────────────────────────────────

test('lookup faction returns matching factions', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    $faction = Faction::factory()->for($campaign)->create([
        'name' => 'Shadow Thieves',
        'alignment' => 'Chaotic Evil',
        'goals' => 'Control the underworld',
    ]);

    Npc::factory()->for($campaign)->create(['faction_id' => $faction->id]);

    $tool = new LookupFaction($campaign);
    $request = new Request(['name' => 'Shadow']);
    $result = $tool->handle($request);

    expect($result)->toContain('Shadow Thieves');
    expect($result)->toContain('Chaotic Evil');
    expect($result)->toContain('Members: 1');
});

test('lookup faction filters by alignment', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Faction::factory()->for($campaign)->create(['name' => 'Holy Order', 'alignment' => 'Lawful Good']);
    Faction::factory()->for($campaign)->create(['name' => 'Dark Guild', 'alignment' => 'Chaotic Evil']);

    $tool = new LookupFaction($campaign);
    $request = new Request(['alignment' => 'Lawful']);
    $result = $tool->handle($request);

    expect($result)->toContain('Holy Order');
    expect($result)->not->toContain('Dark Guild');
});

test('lookup faction returns message when none found', function () {
    $campaign = Campaign::factory()->for(User::factory())->create();

    $tool = new LookupFaction($campaign);
    $request = new Request(['name' => 'Nonexistent']);
    $result = $tool->handle($request);

    expect($result)->toContain('No factions found');
});

// ── Factories ─────────────────────────────────────────────────────────

test('faction factory creates valid factions', function () {
    $faction = Faction::factory()->create();

    expect($faction->name)->toBeString();
    expect($faction->alignment)->toBeString();
});

test('location factory creates valid locations', function () {
    $location = Location::factory()->create();

    expect($location->name)->toBeString();
    expect($location->region)->toBeString();
});

// ── World Event Relationships ─────────────────────────────────────────

test('world event can belong to an npc', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $npc = Npc::factory()->for($campaign)->create();

    $event = $campaign->worldEvents()->create([
        'title' => 'NPC made a decision',
        'description' => 'Decided to betray the guild',
        'event_type' => 'npc_decision',
        'npc_id' => $npc->id,
        'occurred_at' => now(),
    ]);

    expect($event->npc->id)->toBe($npc->id);
    expect($npc->worldEvents)->toHaveCount(1);
});

test('faction has world events relationship', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $faction = Faction::factory()->for($campaign)->create();

    $campaign->worldEvents()->create([
        'title' => 'Faction expanded',
        'description' => 'Took over new territory',
        'event_type' => 'faction_action',
        'faction_id' => $faction->id,
        'occurred_at' => now(),
    ]);

    expect($faction->worldEvents)->toHaveCount(1);
});

test('location has world events relationship', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $location = Location::factory()->for($campaign)->create();

    $campaign->worldEvents()->create([
        'title' => 'Location discovered',
        'description' => 'Ancient ruins were found',
        'event_type' => 'location_development',
        'location_id' => $location->id,
        'occurred_at' => now(),
    ]);

    expect($location->worldEvents)->toHaveCount(1);
});
