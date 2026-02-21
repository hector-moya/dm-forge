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
        ->test('pages::campaigns.wizard')
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
        ->test('pages::campaigns.wizard')
        ->call('nextStep')
        ->assertHasErrors(['name']);
});

test('wizard can add and remove factions', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.wizard')
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
        ->test('pages::campaigns.wizard')
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
        ->test('pages::campaigns.wizard')
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
        ->test('pages::campaigns.wizard')
        ->set('name', 'Epic Quest')
        ->set('premise', 'Save the world')
        ->set('theme_tone', 'Dark fantasy');

    // Add a lore entry
    $component->set('loreName', 'Ancient History')
        ->set('loreDescription', 'Long ago the world was different')
        ->call('addLoreEntry');

    // Add a world rule
    $component->set('worldRuleName', 'No Resurrection')
        ->set('worldRuleDescription', 'The dead stay dead')
        ->call('addWorldRuleEntry');

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
    expect($campaign->lores()->count())->toBe(1);
    expect($campaign->worldRules()->count())->toBe(1);
    expect($campaign->factions()->count())->toBe(1);
    expect($campaign->locations()->count())->toBe(1);
    expect($campaign->characters()->count())->toBe(1);
});

test('wizard ai suggests world details', function () {
    $user = User::factory()->create();

    CampaignWizardAgent::fake([
        [
            'lore_entries' => [
                ['name' => 'The Age of Dragons', 'description' => 'In the age of dragons, the world was young.', 'dm_notes' => ''],
            ],
            'world_rule_entries' => [
                ['name' => 'Moon Magic', 'description' => 'Magic is tied to the phases of the moon.', 'dm_notes' => ''],
            ],
        ],
    ]);

    Livewire::actingAs($user)
        ->test('pages::campaigns.wizard')
        ->set('name', 'Dragon Age')
        ->set('premise', 'Dragons return')
        ->call('suggestWorld')
        ->assertCount('loreEntries', 1)
        ->assertCount('worldRuleEntries', 1);

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
        ->test('pages::campaigns.wizard')
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
        ->test('pages::campaigns.wizard')
        ->set('name', 'Dragon Age')
        ->call('suggestLocations')
        ->assertCount('locations', 1);
});

test('wizard can add and remove lore entries', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.wizard')
        ->set('loreName', 'The Ancient War')
        ->set('loreDescription', 'A war that shaped the realm')
        ->call('addLoreEntry')
        ->assertCount('loreEntries', 1)
        ->call('removeLoreEntry', 0)
        ->assertCount('loreEntries', 0);
});

test('wizard can add and remove world rule entries', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.wizard')
        ->set('worldRuleName', 'No Resurrection Magic')
        ->set('worldRuleDescription', 'The dead stay dead in this world')
        ->call('addWorldRuleEntry')
        ->assertCount('worldRuleEntries', 1)
        ->call('removeWorldRuleEntry', 0)
        ->assertCount('worldRuleEntries', 0);
});

test('wizard can add and remove special mechanics', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.wizard')
        ->set('specialMechanicName', 'Corruption System')
        ->set('specialMechanicDescription', 'Players accumulate corruption points')
        ->call('addSpecialMechanic')
        ->assertCount('specialMechanics', 1)
        ->call('removeSpecialMechanic', 0)
        ->assertCount('specialMechanics', 0);
});

test('wizard ai suggests special mechanics', function () {
    $user = User::factory()->create();

    CampaignWizardAgent::fake([
        [
            'special_mechanics' => [
                ['name' => 'Corruption', 'description' => 'Corruption system', 'dm_notes' => ''],
            ],
        ],
    ]);

    Livewire::actingAs($user)
        ->test('pages::campaigns.wizard')
        ->set('name', 'Dragon Age')
        ->call('suggestSpecialMechanics')
        ->assertCount('specialMechanics', 1);

    CampaignWizardAgent::assertPrompted(fn ($prompt) => $prompt->contains('special mechanics'));
});
