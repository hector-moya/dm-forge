<?php

use App\Ai\Agents\FactionGenerator;
use App\Ai\Agents\ImagePromptCrafter;
use App\Models\Campaign;
use App\Models\Faction;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Image;
use Livewire\Livewire;

test('faction manager page loads for campaign owner', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('campaigns.factions', $campaign))
        ->assertOk();
});

test('faction manager denies access to non-owner', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $campaign = Campaign::factory()->for($other)->create();

    $this->actingAs($user)
        ->get(route('campaigns.factions', $campaign))
        ->assertForbidden();
});

test('faction manager lists factions', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    Faction::factory()->for($campaign)->create(['name' => 'Order of the Flame']);

    Livewire::actingAs($user)
        ->test('pages::campaigns.faction-manager', ['campaign' => $campaign])
        ->assertSee('Order of the Flame');
});

test('faction manager can create a faction', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.faction-manager', ['campaign' => $campaign])
        ->call('openForm')
        ->assertSet('showForm', true)
        ->set('factionName', 'Shadow Guild')
        ->set('factionAlignment', 'Chaotic Neutral')
        ->set('factionGoals', 'Control the underworld')
        ->call('save');

    expect($campaign->factions()->where('name', 'Shadow Guild')->exists())->toBeTrue();
});

test('faction manager can edit a faction', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $faction = Faction::factory()->for($campaign)->create(['name' => 'Old Name']);

    Livewire::actingAs($user)
        ->test('pages::campaigns.faction-manager', ['campaign' => $campaign])
        ->call('openForm', $faction->id)
        ->assertSet('factionName', 'Old Name')
        ->set('factionName', 'New Name')
        ->call('save');

    expect($faction->fresh()->name)->toBe('New Name');
});

test('faction manager can delete a faction', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $faction = Faction::factory()->for($campaign)->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.faction-manager', ['campaign' => $campaign])
        ->call('delete', $faction->id);

    expect($campaign->factions()->count())->toBe(0);
});

test('faction manager search filters factions', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    Faction::factory()->for($campaign)->create(['name' => 'Order of the Flame']);
    Faction::factory()->for($campaign)->create(['name' => 'Shadow Guild']);

    Livewire::actingAs($user)
        ->test('pages::campaigns.faction-manager', ['campaign' => $campaign])
        ->set('search', 'Shadow')
        ->assertSee('Shadow Guild')
        ->assertDontSee('Order of the Flame');
});

test('faction manager shows detail flyout with history', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $faction = Faction::factory()->for($campaign)->create(['name' => 'Holy Order']);

    $campaign->worldEvents()->create([
        'title' => 'Order expanded north',
        'description' => 'Claimed new territory',
        'event_type' => 'faction_action',
        'faction_id' => $faction->id,
        'occurred_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test('pages::campaigns.faction-manager', ['campaign' => $campaign])
        ->call('viewFaction', $faction->id)
        ->assertSet('viewingFactionId', $faction->id);
});

test('faction manager generates faction with ai', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    FactionGenerator::fake([
        [
            'name' => 'The Iron Brotherhood',
            'description' => 'A militant order',
            'alignment' => 'Lawful Neutral',
            'goals' => 'Control the iron trade',
            'resources' => 'Forges and militia',
            'relationships' => [],
            'suggested_leader' => 'Commander Bronzebeard',
            'suggested_headquarters' => 'The Foundry',
        ],
    ]);

    Livewire::actingAs($user)
        ->test('pages::campaigns.faction-manager', ['campaign' => $campaign])
        ->call('openGenerateModal')
        ->assertSet('showGenerateModal', true)
        ->set('generateContext', 'A warrior guild')
        ->call('generate')
        ->assertSet('showGenerateModal', false)
        ->assertSet('showForm', true)
        ->assertSet('factionName', 'The Iron Brotherhood')
        ->assertSet('factionAlignment', 'Lawful Neutral');

    FactionGenerator::assertPrompted(fn ($prompt) => $prompt->contains('warrior guild'));
});

test('faction manager can generate image for faction', function () {
    Storage::fake('public');
    ImagePromptCrafter::fake();
    Image::fake();

    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $faction = Faction::factory()->for($campaign)->create(['name' => 'Iron Brotherhood']);

    Livewire::actingAs($user)
        ->test('pages::campaigns.faction-manager', ['campaign' => $campaign])
        ->call('generateImage', $faction->id);

    expect($faction->fresh()->image_path)->not->toBeNull();
    ImagePromptCrafter::assertPrompted(fn ($prompt) => $prompt->contains('faction'));
    Image::assertGenerated(fn () => true);
});

test('faction manager handles ai failure gracefully', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    FactionGenerator::fake(function () {
        throw new \RuntimeException('API error');
    });

    Livewire::actingAs($user)
        ->test('pages::campaigns.faction-manager', ['campaign' => $campaign])
        ->call('openGenerateModal')
        ->call('generate')
        ->assertSet('generating', false)
        ->assertSet('showForm', false);
});
