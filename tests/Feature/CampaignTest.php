<?php

use App\Models\Campaign;
use App\Models\User;
use Livewire\Livewire;

test('guests cannot access campaigns', function () {
    $this->get(route('campaigns.create'))->assertRedirect(route('login'));
});

test('users can create a campaign', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignCreate::class)
        ->set('name', 'The Lost Mine')
        ->set('premise', 'A forgotten mine holds ancient secrets')
        ->set('status', 'active')
        ->call('save')
        ->assertRedirect();

    expect(Campaign::where('name', 'The Lost Mine')->exists())->toBeTrue();
});

test('users can view their campaign', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('campaigns.show', $campaign))
        ->assertOk()
        ->assertSee($campaign->name);
});

test('users cannot view other users campaigns', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $campaign = Campaign::factory()->for($other)->create();

    $this->actingAs($user)
        ->get(route('campaigns.show', $campaign))
        ->assertForbidden();
});

test('users can edit their campaign', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Campaigns\CampaignEdit::class, ['campaign' => $campaign])
        ->set('name', 'Updated Name')
        ->call('save')
        ->assertRedirect();

    expect($campaign->fresh()->name)->toBe('Updated Name');
});

test('dashboard shows user campaigns', function () {
    $user = User::factory()->create();
    Campaign::factory()->for($user)->create(['name' => 'My Campaign']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('My Campaign');
});
