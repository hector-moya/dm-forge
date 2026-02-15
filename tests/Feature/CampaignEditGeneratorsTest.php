<?php

use App\Ai\Agents\FactionGenerator;
use App\Ai\Agents\LocationGenerator;
use App\Models\Campaign;
use App\Models\User;
use Livewire\Livewire;

test('campaign edit generates faction with ai', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    FactionGenerator::fake([
        [
            'name' => 'The Iron Brotherhood',
            'description' => 'A militant order of warriors',
            'alignment' => 'Lawful Neutral',
            'goals' => 'Control the iron trade',
            'resources' => 'Forges and militia',
            'relationships' => [],
            'suggested_leader' => 'Commander Bronzebeard',
            'suggested_headquarters' => 'The Foundry',
        ],
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignEdit::class, ['campaign' => $campaign])
        ->call('openGenerateFactionModal')
        ->assertSet('showGenerateFactionModal', true)
        ->set('generateFactionContext', 'A warrior guild')
        ->call('generateFaction')
        ->assertSet('showGenerateFactionModal', false)
        ->assertSet('showFactionForm', true)
        ->assertSet('factionName', 'The Iron Brotherhood')
        ->assertSet('factionAlignment', 'Lawful Neutral');

    FactionGenerator::assertPrompted(fn ($prompt) => $prompt->contains('warrior guild'));
});

test('campaign edit generates location with ai', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    LocationGenerator::fake([
        [
            'name' => 'The Whispering Woods',
            'description' => 'A dense enchanted forest',
            'region' => 'Northern Marches',
            'history' => 'Once home to ancient elves',
            'notable_features' => ['Stone circle', 'Glowing mushrooms'],
            'suggested_parent_location' => null,
            'suggested_faction' => null,
        ],
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignEdit::class, ['campaign' => $campaign])
        ->call('openGenerateLocationModal')
        ->assertSet('showGenerateLocationModal', true)
        ->set('generateLocationContext', 'A haunted forest')
        ->call('generateLocation')
        ->assertSet('showGenerateLocationModal', false)
        ->assertSet('showLocationForm', true)
        ->assertSet('locationName', 'The Whispering Woods')
        ->assertSet('locationRegion', 'Northern Marches');

    LocationGenerator::assertPrompted(fn ($prompt) => $prompt->contains('haunted forest'));
});

test('campaign edit handles faction generation failure', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    FactionGenerator::fake(function () {
        throw new \RuntimeException('API error');
    });

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignEdit::class, ['campaign' => $campaign])
        ->call('openGenerateFactionModal')
        ->call('generateFaction')
        ->assertSet('generatingFaction', false)
        ->assertSet('showFactionForm', false);
});

test('campaign edit handles location generation failure', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    LocationGenerator::fake(function () {
        throw new \RuntimeException('API error');
    });

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignEdit::class, ['campaign' => $campaign])
        ->call('openGenerateLocationModal')
        ->call('generateLocation')
        ->assertSet('generatingLocation', false)
        ->assertSet('showLocationForm', false);
});
