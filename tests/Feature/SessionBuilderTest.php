<?php

use App\Models\Campaign;
use App\Models\GameSession;
use App\Models\User;
use Livewire\Livewire;

test('users can create a session', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::sessions.builder', ['campaign' => $campaign])
        ->set('title', 'Into the Dungeon')
        ->set('session_number', 1)
        ->set('type', 'sequential')
        ->set('status', 'draft')
        ->call('saveSession')
        ->assertRedirect();

    expect($campaign->gameSessions()->where('title', 'Into the Dungeon')->exists())->toBeTrue();
});

test('users can add a scene to a session', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();

    Livewire::actingAs($user)
        ->test('pages::sessions.builder', ['session' => $session])
        ->call('openAddSceneForm')
        ->assertSet('showAddSceneForm', true)
        ->set('newSceneTitle', 'The Tavern')
        ->set('newSceneDescription', 'A dimly lit tavern')
        ->call('saveNewScene');

    expect($session->scenes()->where('title', 'The Tavern')->exists())->toBeTrue();
});

test('users can add a standalone encounter to a session', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();

    Livewire::actingAs($user)
        ->test('pages::sessions.builder', ['session' => $session])
        ->call('openAddEncounterForm')
        ->assertSet('showAddEncounterForm', true)
        ->set('newEncounterName', 'Goblin Ambush')
        ->set('newEncounterDescription', 'Goblins jump from the bushes')
        ->call('saveNewEncounter');

    expect($session->encounters()->where('name', 'Goblin Ambush')->exists())->toBeTrue();
});

test('users can add a standalone branch to a session', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();

    Livewire::actingAs($user)
        ->test('pages::sessions.builder', ['session' => $session])
        ->call('openAddBranchForm')
        ->assertSet('showAddBranchForm', true)
        ->set('newBranchLabel', 'Fight the dragon')
        ->set('newBranchDescription', 'Engage in combat')
        ->call('saveNewBranch');

    expect($session->branchOptions()->where('label', 'Fight the dragon')->exists())->toBeTrue();
});

test('users can delete a session', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();

    Livewire::actingAs($user)
        ->test('pages::sessions.builder', ['session' => $session])
        ->call('deleteSession')
        ->assertRedirect();

    expect(GameSession::find($session->id))->toBeNull();
});

test('users cannot access other users sessions', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $campaign = Campaign::factory()->for($other)->create();
    $session = GameSession::factory()->for($campaign)->create();

    $this->actingAs($user)
        ->get(route('sessions.edit', $session))
        ->assertForbidden();
});
