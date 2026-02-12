<?php

use App\Ai\Agents\CampaignWizardAgent;
use App\Models\Campaign;
use App\Models\User;
use Livewire\Livewire;

test('guests cannot access campaign wizard', function () {
    $this->get(route('campaigns.wizard'))->assertRedirect(route('login'));
});

test('users can access campaign wizard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('campaigns.wizard'))
        ->assertOk();
});

test('wizard step navigation works', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignWizard::class)
        ->assertSet('currentStep', 1)
        ->set('name', 'Test Campaign')
        ->call('nextStep')
        ->assertSet('currentStep', 2)
        ->call('nextStep')
        ->assertSet('currentStep', 3)
        ->call('previousStep')
        ->assertSet('currentStep', 2);
});

test('wizard requires name on step 1', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignWizard::class)
        ->call('nextStep')
        ->assertHasErrors(['name']);
});

test('wizard can add and remove factions', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignWizard::class)
        ->set('factionName', 'The Iron Guard')
        ->set('factionDescription', 'A military order')
        ->set('factionAlignment', 'Lawful Neutral')
        ->set('factionGoals', 'Maintain order')
        ->call('addFaction')
        ->assertCount('factions', 1)
        ->call('removeFaction', 0)
        ->assertCount('factions', 0);
});

test('wizard can add and remove locations', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignWizard::class)
        ->set('locationName', 'Dragon Keep')
        ->set('locationDescription', 'A fortress atop a mountain')
        ->set('locationRegion', 'Northern Peaks')
        ->call('addLocation')
        ->assertCount('locations', 1)
        ->call('removeLocation', 0)
        ->assertCount('locations', 0);
});

test('wizard can add and remove characters', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignWizard::class)
        ->set('characterName', 'Thorin')
        ->set('characterPlayerName', 'John')
        ->set('characterClass', 'Fighter')
        ->set('characterLevel', 5)
        ->call('addCharacter')
        ->assertCount('characters', 1)
        ->call('removeCharacter', 0)
        ->assertCount('characters', 0);
});

test('wizard creates campaign with all entities', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignWizard::class)
        ->set('name', 'Epic Quest')
        ->set('premise', 'Save the world')
        ->set('theme_tone', 'Dark fantasy')
        ->set('lore', 'An ancient evil stirs')
        ->set('world_rules', 'No resurrection magic');

    // Add a faction
    $component->set('factionName', 'Dark Brotherhood')
        ->set('factionAlignment', 'Chaotic Evil')
        ->call('addFaction');

    // Add a location
    $component->set('locationName', 'Shadow Keep')
        ->set('locationRegion', 'Darklands')
        ->call('addLocation');

    // Add a character
    $component->set('characterName', 'Elara')
        ->set('characterClass', 'Wizard')
        ->set('characterLevel', 3)
        ->call('addCharacter');

    // Create campaign
    $component->call('createCampaign')
        ->assertRedirect();

    $campaign = Campaign::where('name', 'Epic Quest')->first();
    expect($campaign)->not->toBeNull();
    expect($campaign->premise)->toBe('Save the world');
    expect($campaign->factions()->count())->toBe(1);
    expect($campaign->locations()->count())->toBe(1);
    expect($campaign->characters()->count())->toBe(1);
});

test('wizard ai suggests world details', function () {
    $user = User::factory()->create();

    CampaignWizardAgent::fake([
        [
            'lore' => 'In the age of dragons, the world was young.',
            'world_rules' => 'Magic is tied to the phases of the moon.',
        ],
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignWizard::class)
        ->set('name', 'Dragon Age')
        ->set('premise', 'Dragons return')
        ->call('suggestWorld')
        ->assertSet('lore', 'In the age of dragons, the world was young.')
        ->assertSet('world_rules', 'Magic is tied to the phases of the moon.');

    CampaignWizardAgent::assertPrompted(fn ($prompt) => $prompt->contains('world'));
});

test('wizard ai suggests factions', function () {
    $user = User::factory()->create();

    CampaignWizardAgent::fake([
        [
            'factions' => [
                ['name' => 'The Dragonguard', 'description' => 'Protectors', 'alignment' => 'Lawful Good', 'goals' => 'Defend'],
                ['name' => 'Shadow Cult', 'description' => 'Worshippers', 'alignment' => 'Chaotic Evil', 'goals' => 'Summon'],
            ],
        ],
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignWizard::class)
        ->set('name', 'Dragon Age')
        ->call('suggestFactions')
        ->assertCount('factions', 2);
});

test('wizard ai suggests locations', function () {
    $user = User::factory()->create();

    CampaignWizardAgent::fake([
        [
            'locations' => [
                ['name' => 'Dragon Peak', 'description' => 'A mountain', 'region' => 'North'],
            ],
        ],
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignWizard::class)
        ->set('name', 'Dragon Age')
        ->call('suggestLocations')
        ->assertCount('locations', 1);
});
