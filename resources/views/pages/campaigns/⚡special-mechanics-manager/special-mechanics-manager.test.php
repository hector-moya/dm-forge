<?php

use App\Models\Campaign;
use App\Models\SpecialMechanic;
use App\Models\User;
use Livewire\Livewire;

test('special mechanics manager page loads for campaign owner', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('campaigns.special-mechanics', $campaign))
        ->assertOk();
});

test('special mechanics manager denies access to non-owner', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $campaign = Campaign::factory()->for($other)->create();

    $this->actingAs($user)
        ->get(route('campaigns.special-mechanics', $campaign))
        ->assertForbidden();
});

test('special mechanics manager lists mechanics for campaign', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $mechanic = SpecialMechanic::factory()->for($user)->create(['name' => 'Sanity Points']);
    $campaign->specialMechanics()->attach($mechanic->id);

    Livewire::actingAs($user)
        ->test('pages::campaigns.special-mechanics-manager', ['campaign' => $campaign])
        ->assertSee('Sanity Points');
});

test('special mechanics manager does not show mechanics from other campaigns', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $otherCampaign = Campaign::factory()->for($user)->create();
    $mechanic = SpecialMechanic::factory()->for($user)->create(['name' => 'Other Mechanic']);
    $otherCampaign->specialMechanics()->attach($mechanic->id);

    Livewire::actingAs($user)
        ->test('pages::campaigns.special-mechanics-manager', ['campaign' => $campaign])
        ->assertDontSee('Other Mechanic');
});

test('special mechanics manager can create a mechanic', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.special-mechanics-manager', ['campaign' => $campaign])
        ->set('form.name', 'Corruption Mechanic')
        ->set('form.description', 'Players accumulate corruption points')
        ->call('save');

    expect($campaign->specialMechanics()->where('name', 'Corruption Mechanic')->exists())->toBeTrue();
});

test('special mechanics manager can edit a mechanic', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $mechanic = SpecialMechanic::factory()->for($user)->create(['name' => 'Old Mechanic']);
    $campaign->specialMechanics()->attach($mechanic->id);

    Livewire::actingAs($user)
        ->test('pages::campaigns.special-mechanics-manager', ['campaign' => $campaign])
        ->call('setMechanicId', $mechanic->id)
        ->assertSet('form.name', 'Old Mechanic')
        ->set('form.name', 'New Mechanic')
        ->call('save');

    expect($mechanic->fresh()->name)->toBe('New Mechanic');
});

test('special mechanics manager can delete a mechanic', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $mechanic = SpecialMechanic::factory()->for($user)->create();
    $campaign->specialMechanics()->attach($mechanic->id);

    Livewire::actingAs($user)
        ->test('pages::campaigns.special-mechanics-manager', ['campaign' => $campaign])
        ->call('form.destroy', $mechanic);

    expect($campaign->specialMechanics()->count())->toBe(0);
});

test('special mechanics manager search filters results', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $mechanicA = SpecialMechanic::factory()->for($user)->create(['name' => 'Sanity Points']);
    $mechanicB = SpecialMechanic::factory()->for($user)->create(['name' => 'Corruption System']);
    $campaign->specialMechanics()->attach([$mechanicA->id, $mechanicB->id]);

    Livewire::actingAs($user)
        ->test('pages::campaigns.special-mechanics-manager', ['campaign' => $campaign])
        ->set('search', 'Corruption')
        ->assertSee('Corruption System')
        ->assertDontSee('Sanity Points');
});

test('special mechanics manager can manage rules for a mechanic', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $mechanic = SpecialMechanic::factory()->for($user)->create(['name' => 'Sanity Points']);
    $campaign->specialMechanics()->attach($mechanic->id);

    Livewire::actingAs($user)
        ->test('pages::campaigns.special-mechanics-manager', ['campaign' => $campaign])
        ->call('openRulesPanel', $mechanic->id)
        ->assertSet('editingMechanicForRulesId', $mechanic->id)
        ->call('openRuleForm')
        ->assertSet('showRuleForm', true)
        ->set('ruleName', 'Gain Sanity')
        ->set('ruleDescription', 'Gained when performing good deeds')
        ->call('saveRule');

    expect($mechanic->fresh()->rules()->where('name', 'Gain Sanity')->exists())->toBeTrue();
});

test('special mechanics manager can delete a rule', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $mechanic = SpecialMechanic::factory()->for($user)->create();
    $campaign->specialMechanics()->attach($mechanic->id);
    $rule = $mechanic->rules()->create(['name' => 'Rule To Delete', 'description' => null, 'notes' => null]);

    Livewire::actingAs($user)
        ->test('pages::campaigns.special-mechanics-manager', ['campaign' => $campaign])
        ->call('openRulesPanel', $mechanic->id)
        ->call('deleteRule', $rule->id);

    expect($mechanic->fresh()->rules()->count())->toBe(0);
});
