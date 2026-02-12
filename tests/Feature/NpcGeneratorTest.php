<?php

use App\Ai\Agents\NpcGenerator;
use App\Models\Campaign;
use App\Models\Npc;
use App\Models\User;
use Livewire\Livewire;

test('npc generator agent implements structured output', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    $agent = new NpcGenerator($campaign);

    expect($agent)->toBeInstanceOf(\Laravel\Ai\Contracts\HasStructuredOutput::class);
    expect($agent)->toBeInstanceOf(\Laravel\Ai\Contracts\HasTools::class);
    expect($agent->instructions())->toContain('D&D NPC designer');
});

test('npc generator populates form with ai response', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    NpcGenerator::fake([
        [
            'name' => 'Gornik the Bold',
            'role' => 'Tavern Owner',
            'description' => 'A burly half-orc with a missing tusk',
            'personality' => 'Jovial but quick to anger',
            'motivation' => 'Protect his establishment at all costs',
            'voice_description' => 'Deep, rumbling bass with a slight lisp',
            'speech_patterns' => 'Short, declarative sentences with occasional grunts',
            'catchphrases' => ['You break it, you buy it!', 'Drink up or get out!'],
            'suggested_faction' => 'Merchants Guild',
            'suggested_location' => 'The Rusty Tankard',
        ],
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignEdit::class, ['campaign' => $campaign])
        ->call('openGenerateNpcModal')
        ->assertSet('showGenerateNpcModal', true)
        ->set('generateNpcContext', 'A tavern owner in the docks district')
        ->call('generateNpc')
        ->assertSet('showGenerateNpcModal', false)
        ->assertSet('showNpcForm', true)
        ->assertSet('npcName', 'Gornik the Bold')
        ->assertSet('npcRole', 'Tavern Owner')
        ->assertSet('npcDescription', 'A burly half-orc with a missing tusk')
        ->assertSet('npcPersonality', 'Jovial but quick to anger')
        ->assertSet('npcMotivation', 'Protect his establishment at all costs')
        ->assertSet('npcVoiceDescription', 'Deep, rumbling bass with a slight lisp')
        ->assertSet('npcSpeechPatterns', 'Short, declarative sentences with occasional grunts');

    NpcGenerator::assertPrompted(fn ($prompt) => $prompt->contains('tavern owner'));
});

test('npc can be saved with voice fields', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignEdit::class, ['campaign' => $campaign])
        ->call('openNpcForm')
        ->set('npcName', 'Test NPC')
        ->set('npcRole', 'Guard')
        ->set('npcVoiceDescription', 'Gruff and commanding')
        ->set('npcSpeechPatterns', 'Military jargon')
        ->set('npcCatchphrases', "Halt!\nWho goes there?")
        ->call('saveNpc');

    $npc = $campaign->npcs()->where('name', 'Test NPC')->first();
    expect($npc)->not->toBeNull();
    expect($npc->voice_description)->toBe('Gruff and commanding');
    expect($npc->speech_patterns)->toBe('Military jargon');
    expect($npc->catchphrases)->toBe(['Halt!', 'Who goes there?']);
});

test('npc voice fields can be edited', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $npc = $campaign->npcs()->create([
        'name' => 'Existing NPC',
        'role' => 'Merchant',
        'voice_description' => 'Original voice',
        'speech_patterns' => 'Original patterns',
        'catchphrases' => ['Old phrase'],
        'is_alive' => true,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignEdit::class, ['campaign' => $campaign])
        ->call('openNpcForm', $npc->id)
        ->assertSet('npcVoiceDescription', 'Original voice')
        ->assertSet('npcSpeechPatterns', 'Original patterns')
        ->set('npcVoiceDescription', 'Updated voice')
        ->set('npcCatchphrases', "New phrase 1\nNew phrase 2")
        ->call('saveNpc');

    $npc->refresh();
    expect($npc->voice_description)->toBe('Updated voice');
    expect($npc->catchphrases)->toBe(['New phrase 1', 'New phrase 2']);
});

test('npc generator handles ai failure gracefully', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    NpcGenerator::fake(function () {
        throw new \RuntimeException('API rate limit exceeded');
    });

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignEdit::class, ['campaign' => $campaign])
        ->call('openGenerateNpcModal')
        ->call('generateNpc')
        ->assertSet('generatingNpc', false)
        ->assertSet('showNpcForm', false);
});

test('npc factory creates valid npcs', function () {
    $npc = Npc::factory()->create();

    expect($npc->name)->toBeString();
    expect($npc->is_alive)->toBeTrue();
});

test('npc factory withVoice state includes voice fields', function () {
    $npc = Npc::factory()->withVoice()->create();

    expect($npc->voice_description)->not->toBeNull();
    expect($npc->speech_patterns)->not->toBeNull();
    expect($npc->catchphrases)->toBeArray();
});
