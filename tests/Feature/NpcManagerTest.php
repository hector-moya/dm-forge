<?php

use App\Ai\Agents\NpcGenerator;
use App\Models\Campaign;
use App\Models\Faction;
use App\Models\Location;
use App\Models\Npc;
use App\Models\User;
use Livewire\Livewire;

test('npc manager page loads for campaign owner', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('campaigns.npcs', $campaign))
        ->assertOk();
});

test('npc manager denies access to non-owner', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $campaign = Campaign::factory()->for($other)->create();

    $this->actingAs($user)
        ->get(route('campaigns.npcs', $campaign))
        ->assertForbidden();
});

test('npc manager lists npcs', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    Npc::factory()->for($campaign)->create(['name' => 'Gornik the Bold']);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\NpcManager::class, ['campaign' => $campaign])
        ->assertSee('Gornik the Bold');
});

test('npc manager can create an npc', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\NpcManager::class, ['campaign' => $campaign])
        ->call('openForm')
        ->assertSet('showForm', true)
        ->set('npcName', 'Elena Darkwood')
        ->set('npcRole', 'Ranger')
        ->set('npcDescription', 'A skilled ranger from the north')
        ->call('save');

    $npc = $campaign->npcs()->where('name', 'Elena Darkwood')->first();
    expect($npc)->not->toBeNull();
    expect($npc->role)->toBe('Ranger');
});

test('npc manager can create npc with faction and location', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $faction = Faction::factory()->for($campaign)->create();
    $location = Location::factory()->for($campaign)->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\NpcManager::class, ['campaign' => $campaign])
        ->call('openForm')
        ->set('npcName', 'Guard Captain')
        ->set('npcFactionId', $faction->id)
        ->set('npcLocationId', $location->id)
        ->call('save');

    $npc = $campaign->npcs()->where('name', 'Guard Captain')->first();
    expect($npc->faction_id)->toBe($faction->id);
    expect($npc->location_id)->toBe($location->id);
});

test('npc manager can edit an npc', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $npc = Npc::factory()->for($campaign)->create(['name' => 'Old Name', 'role' => 'Guard']);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\NpcManager::class, ['campaign' => $campaign])
        ->call('openForm', $npc->id)
        ->assertSet('npcName', 'Old Name')
        ->assertSet('npcRole', 'Guard')
        ->set('npcName', 'New Name')
        ->call('save');

    expect($npc->fresh()->name)->toBe('New Name');
});

test('npc manager can delete an npc', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $npc = Npc::factory()->for($campaign)->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\NpcManager::class, ['campaign' => $campaign])
        ->call('delete', $npc->id);

    expect($campaign->npcs()->count())->toBe(0);
});

test('npc manager search filters npcs', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    Npc::factory()->for($campaign)->create(['name' => 'Gornik the Bold']);
    Npc::factory()->for($campaign)->create(['name' => 'Elena Darkwood']);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\NpcManager::class, ['campaign' => $campaign])
        ->set('search', 'Elena')
        ->assertSee('Elena Darkwood')
        ->assertDontSee('Gornik the Bold');
});

test('npc manager faction filter works', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $faction = Faction::factory()->for($campaign)->create();
    Npc::factory()->for($campaign)->create(['name' => 'Member', 'faction_id' => $faction->id]);
    Npc::factory()->for($campaign)->create(['name' => 'Loner', 'faction_id' => null]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\NpcManager::class, ['campaign' => $campaign])
        ->set('factionFilter', $faction->id)
        ->assertSee('Member')
        ->assertDontSee('Loner');
});

test('npc manager alive filter works', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    Npc::factory()->for($campaign)->create(['name' => 'Living NPC', 'is_alive' => true]);
    Npc::factory()->for($campaign)->create(['name' => 'Dead NPC', 'is_alive' => false]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\NpcManager::class, ['campaign' => $campaign])
        ->set('aliveFilter', 'dead')
        ->assertSee('Dead NPC')
        ->assertDontSee('Living NPC');
});

test('npc manager saves voice fields and catchphrases', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\NpcManager::class, ['campaign' => $campaign])
        ->call('openForm')
        ->set('npcName', 'Voiced NPC')
        ->set('npcVoiceDescription', 'Deep, rumbling')
        ->set('npcSpeechPatterns', 'Short sentences')
        ->set('npcCatchphrases', "Halt!\nWho goes there?")
        ->call('save');

    $npc = $campaign->npcs()->where('name', 'Voiced NPC')->first();
    expect($npc->voice_description)->toBe('Deep, rumbling');
    expect($npc->speech_patterns)->toBe('Short sentences');
    expect($npc->catchphrases)->toBe(['Halt!', 'Who goes there?']);
});

test('npc manager shows detail flyout with history', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $npc = Npc::factory()->for($campaign)->create(['name' => 'Test NPC']);

    $campaign->worldEvents()->create([
        'title' => 'NPC betrayed the guild',
        'description' => 'Stole the treasury',
        'event_type' => 'npc_decision',
        'npc_id' => $npc->id,
        'occurred_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\NpcManager::class, ['campaign' => $campaign])
        ->call('viewNpc', $npc->id)
        ->assertSet('viewingNpcId', $npc->id);
});

test('npc manager generates npc with ai', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    NpcGenerator::fake([
        [
            'name' => 'Gornik the Bold',
            'role' => 'Tavern Owner',
            'description' => 'A burly half-orc',
            'personality' => 'Jovial but quick to anger',
            'motivation' => 'Protect his establishment',
            'voice_description' => 'Deep bass',
            'speech_patterns' => 'Short sentences',
            'catchphrases' => ['Drink up!', 'You break it, you buy it!'],
            'backstory' => 'Former adventurer who settled down',
            'suggested_faction' => null,
            'suggested_location' => null,
        ],
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\NpcManager::class, ['campaign' => $campaign])
        ->call('openGenerateModal')
        ->assertSet('showGenerateModal', true)
        ->set('generateContext', 'A tavern owner')
        ->call('generate')
        ->assertSet('showGenerateModal', false)
        ->assertSet('showForm', true)
        ->assertSet('npcName', 'Gornik the Bold')
        ->assertSet('npcRole', 'Tavern Owner')
        ->assertSet('npcVoiceDescription', 'Deep bass');

    NpcGenerator::assertPrompted(fn ($prompt) => $prompt->contains('tavern owner'));
});

test('npc manager handles ai failure gracefully', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    NpcGenerator::fake(function () {
        throw new \RuntimeException('API error');
    });

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\NpcManager::class, ['campaign' => $campaign])
        ->call('openGenerateModal')
        ->call('generate')
        ->assertSet('generating', false)
        ->assertSet('showForm', false);
});
