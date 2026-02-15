<?php

use App\Models\Campaign;
use App\Models\GameSession;
use App\Models\Scene;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->campaign = Campaign::factory()->create(['user_id' => $this->user->id]);
    $this->session = GameSession::factory()->create(['campaign_id' => $this->campaign->id]);
    $this->scene = Scene::factory()->create(['game_session_id' => $this->session->id]);
    
    $this->actingAs($this->user);
});

it('renders successfully', function () {
    Livewire::test('sessions.scene-card', [
        'scene' => $this->scene,
        'sessionId' => $this->session->id,
    ])
        ->assertStatus(200);
});

it('can add an encounter to a scene', function () {
    Livewire::test('sessions.scene-card', [
        'scene' => $this->scene,
        'sessionId' => $this->session->id,
    ])
        ->set('newEncounterName', 'Goblin Ambush')
        ->set('newEncounterDescription', 'A group of goblins attacks from the trees')
        ->set('newEncounterEnvironment', 'Forest')
        ->call('saveNewEncounter')
        ->assertDispatched('$refresh');

    expect($this->scene->encounters()->count())->toBe(1);
    expect($this->scene->encounters()->first()->name)->toBe('Goblin Ambush');
});

it('can add a branch option to a scene', function () {
    Livewire::test('sessions.scene-card', [
        'scene' => $this->scene,
        'sessionId' => $this->session->id,
    ])
        ->set('newBranchLabel', 'Take the left path')
        ->set('newBranchDescription', 'The path leads to a dark cave')
        ->call('saveNewBranch')
        ->assertDispatched('$refresh');

    expect($this->scene->branchOptions()->count())->toBe(1);
    expect($this->scene->branchOptions()->first()->label)->toBe('Take the left path');
});

it('validates encounter name is required', function () {
    Livewire::test('sessions.scene-card', [
        'scene' => $this->scene,
        'sessionId' => $this->session->id,
    ])
        ->set('newEncounterName', '')
        ->call('saveNewEncounter')
        ->assertHasErrors(['newEncounterName' => 'required']);
});

it('validates branch label is required', function () {
    Livewire::test('sessions.scene-card', [
        'scene' => $this->scene,
        'sessionId' => $this->session->id,
    ])
        ->set('newBranchLabel', '')
        ->call('saveNewBranch')
        ->assertHasErrors(['newBranchLabel' => 'required']);
});
