<?php

use App\Models\Campaign;
use App\Models\Character;
use App\Models\User;
use Livewire\Livewire;

it('renders the character index page with characters', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $character = Character::factory()->for($campaign)->create();

    $this->actingAs($user)
        ->get(route('campaigns.characters', $campaign))
        ->assertSuccessful()
        ->assertSeeLivewire('pages::characters.index');
});

it('renders edit links with correct route parameters', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $character = Character::factory()->for($campaign)->create();

    Livewire::actingAs($user)
        ->test('pages::characters.index', ['campaign' => $campaign])
        ->assertSeeHtml(route('characters.edit', [$campaign, $character]))
        ->assertSuccessful();
});

it('renders empty state when no characters exist', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::characters.index', ['campaign' => $campaign])
        ->assertSee('No characters yet')
        ->assertSuccessful();
});

it('forbids access to another user campaign characters', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $campaign = Campaign::factory()->for($otherUser)->create();

    $this->actingAs($user)
        ->get(route('campaigns.characters', $campaign))
        ->assertForbidden();
});
