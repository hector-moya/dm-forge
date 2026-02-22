<?php

use App\Models\Campaign;
use App\Models\User;
use App\Models\WorldRule;
use Livewire\Livewire;

test('world rules manager page loads for campaign owner', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('campaigns.world-rules', $campaign))
        ->assertOk();
});

test('world rules manager denies access to non-owner', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $campaign = Campaign::factory()->for($other)->create();

    $this->actingAs($user)
        ->get(route('campaigns.world-rules', $campaign))
        ->assertForbidden();
});

test('world rules manager lists world rules for campaign', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $worldRule = WorldRule::factory()->for($user)->create(['name' => 'Magic Fades at Dawn']);
    $campaign->worldRules()->attach($worldRule->id);

    Livewire::actingAs($user)
        ->test('pages::campaigns.world-rules-manager', ['campaign' => $campaign])
        ->assertSee('Magic Fades at Dawn');
});

test('world rules manager does not show rules from other campaigns', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $otherCampaign = Campaign::factory()->for($user)->create();
    $worldRule = WorldRule::factory()->for($user)->create(['name' => 'Other Campaign Rule']);
    $otherCampaign->worldRules()->attach($worldRule->id);

    Livewire::actingAs($user)
        ->test('pages::campaigns.world-rules-manager', ['campaign' => $campaign])
        ->assertDontSee('Other Campaign Rule');
});

test('world rules manager can create a world rule', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.world-rules-manager', ['campaign' => $campaign])
        ->set('form.name', 'No Divine Magic')
        ->set('form.description', 'The gods have abandoned this realm')
        ->call('save');

    expect($campaign->worldRules()->where('name', 'No Divine Magic')->exists())->toBeTrue();
});

test('world rules manager can edit a world rule', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $worldRule = WorldRule::factory()->for($user)->create(['name' => 'Old Rule']);
    $campaign->worldRules()->attach($worldRule->id);

    Livewire::actingAs($user)
        ->test('pages::campaigns.world-rules-manager', ['campaign' => $campaign])
        ->call('setWorldRuleId', $worldRule->id)
        ->assertSet('form.name', 'Old Rule')
        ->set('form.name', 'New Rule')
        ->call('save');

    expect($worldRule->fresh()->name)->toBe('New Rule');
});

test('world rules manager can delete a world rule', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $worldRule = WorldRule::factory()->for($user)->create();
    $campaign->worldRules()->attach($worldRule->id);

    Livewire::actingAs($user)
        ->test('pages::campaigns.world-rules-manager', ['campaign' => $campaign])
        ->call('form.destroy', $worldRule);

    expect($campaign->worldRules()->count())->toBe(0);
});

test('world rules manager search filters results', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $ruleA = WorldRule::factory()->for($user)->create(['name' => 'Magic Fades at Dawn']);
    $ruleB = WorldRule::factory()->for($user)->create(['name' => 'Dragons Rule']);
    $campaign->worldRules()->attach([$ruleA->id, $ruleB->id]);

    Livewire::actingAs($user)
        ->test('pages::campaigns.world-rules-manager', ['campaign' => $campaign])
        ->set('search', 'Dragons')
        ->assertSee('Dragons Rule')
        ->assertDontSee('Magic Fades at Dawn');
});
