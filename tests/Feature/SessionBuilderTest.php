<?php

use App\Models\Campaign;
use App\Models\GameSession;
use App\Models\User;
use Livewire\Livewire;

test('users can create a session', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Sessions\SessionBuilder::class, ['campaign' => $campaign])
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
        ->test(\App\Livewire\Sessions\SessionBuilder::class, ['session' => $session])
        ->call('openSceneForm')
        ->set('sceneTitle', 'The Tavern')
        ->set('sceneDescription', 'A dimly lit tavern')
        ->call('saveScene');

    expect($session->scenes()->where('title', 'The Tavern')->exists())->toBeTrue();
});

test('users can add an encounter to a session', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $scene = $session->scenes()->create(['title' => 'Forest Path', 'sort_order' => 1]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Sessions\SessionBuilder::class, ['session' => $session])
        ->call('openEncounterForm', $scene->id)
        ->set('encounterName', 'Goblin Ambush')
        ->set('encounterDifficulty', 'medium')
        ->call('saveEncounter');

    expect($session->encounters()->where('name', 'Goblin Ambush')->exists())->toBeTrue();
});

test('users can add monsters to an encounter', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = $session->encounters()->create(['name' => 'Test Encounter', 'difficulty' => 'easy']);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Sessions\SessionBuilder::class, ['session' => $session])
        ->call('openMonsterForm', $encounter->id)
        ->set('monsterName', 'Goblin')
        ->set('monsterHpMax', 7)
        ->set('monsterAc', 15)
        ->set('monsterCount', 3)
        ->call('saveMonster');

    expect($encounter->monsters()->count())->toBe(3);
    expect($encounter->monsters()->first()->name)->toBe('Goblin 1');
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
