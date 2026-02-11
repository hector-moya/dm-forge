<?php

use App\Models\Campaign;
use App\Models\Character;
use App\Models\GameSession;
use App\Models\User;
use Livewire\Livewire;

test('running a prepared session sets status to running', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->prepared()->create();

    $this->actingAs($user)->get(route('sessions.run', $session));

    expect($session->fresh()->status)->toBe('running');
    expect($session->fresh()->started_at)->not->toBeNull();
});

test('users can add characters to initiative', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();
    $character = Character::factory()->for($campaign)->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Sessions\SessionRunner::class, ['session' => $session])
        ->call('addCharacterToCombat', $character->id)
        ->assertSet('combatants.0.name', $character->name);
});

test('users can add log entries', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Sessions\SessionRunner::class, ['session' => $session])
        ->set('logEntry', 'The party enters the dungeon')
        ->set('logType', 'narrative')
        ->call('addLogEntry');

    expect($session->sessionLogs()->where('entry', 'The party enters the dungeon')->exists())->toBeTrue();
});

test('users can adjust combatant HP', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();
    $character = Character::factory()->for($campaign)->create(['hp_max' => 50, 'hp_current' => 50]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Sessions\SessionRunner::class, ['session' => $session])
        ->call('addCharacterToCombat', $character->id)
        ->call('adjustHp', 0, -10);

    expect($component->get('combatants.0.hp_current'))->toBe(40);
});

test('HP cannot go below zero', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();
    $character = Character::factory()->for($campaign)->create(['hp_max' => 10, 'hp_current' => 5]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Sessions\SessionRunner::class, ['session' => $session])
        ->call('addCharacterToCombat', $character->id)
        ->call('adjustHp', 0, -100);

    expect($component->get('combatants.0.hp_current'))->toBe(0);
});

test('users can end a session', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Sessions\SessionRunner::class, ['session' => $session])
        ->call('endSession');

    expect($session->fresh()->status)->toBe('completed');
    expect($session->fresh()->ended_at)->not->toBeNull();
});

test('users can toggle scene reveal', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();
    $scene = $session->scenes()->create(['title' => 'Test Scene', 'sort_order' => 1, 'is_revealed' => false]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Sessions\SessionRunner::class, ['session' => $session])
        ->call('toggleSceneReveal', $scene->id);

    expect($scene->fresh()->is_revealed)->toBeTrue();
});

test('users cannot access other users sessions', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $campaign = Campaign::factory()->for($other)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();

    $this->actingAs($user)
        ->get(route('sessions.run', $session))
        ->assertForbidden();
});
