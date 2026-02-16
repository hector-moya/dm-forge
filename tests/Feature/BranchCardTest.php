<?php

use App\Models\BranchOption;
use App\Models\Campaign;
use App\Models\GameSession;
use App\Models\Scene;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->campaign = Campaign::factory()->for($this->user)->create();
    $this->session = GameSession::factory()->for($this->campaign)->create();
    $this->scene = Scene::factory()->for($this->session)->create(['sort_order' => 1]);

    $this->actingAs($this->user);
});

test('branch option stores destination scene id', function () {
    $destination = Scene::factory()->for($this->session)->create(['sort_order' => 2, 'title' => 'Boss Room']);

    $branch = $this->scene->branchOptions()->create([
        'label' => 'Enter the boss room',
        'game_session_id' => $this->session->id,
        'destination_scene_id' => $destination->id,
    ]);

    expect($branch->destinationScene->id)->toBe($destination->id)
        ->and($branch->destinationScene->title)->toBe('Boss Room');
});

test('branch card saves destination scene id', function () {
    $destination = Scene::factory()->for($this->session)->create(['sort_order' => 2]);

    $branch = $this->scene->branchOptions()->create([
        'label' => 'Go north',
        'game_session_id' => $this->session->id,
    ]);

    Livewire::test('sessions.branch-card', ['branch' => $branch])
        ->call('openForm')
        ->set('destinationSceneId', $destination->id)
        ->call('save');

    expect($branch->fresh()->destination_scene_id)->toBe($destination->id);
});

test('branch card clears destination scene id', function () {
    $destination = Scene::factory()->for($this->session)->create(['sort_order' => 2]);

    $branch = $this->scene->branchOptions()->create([
        'label' => 'Go north',
        'game_session_id' => $this->session->id,
        'destination_scene_id' => $destination->id,
    ]);

    Livewire::test('sessions.branch-card', ['branch' => $branch])
        ->call('openForm')
        ->set('destinationSceneId', null)
        ->call('save');

    expect($branch->fresh()->destination_scene_id)->toBeNull();
});

test('destination scene id is nullified when scene is deleted', function () {
    $destination = Scene::factory()->for($this->session)->create(['sort_order' => 2]);

    $branch = $this->scene->branchOptions()->create([
        'label' => 'Go to treasure room',
        'game_session_id' => $this->session->id,
        'destination_scene_id' => $destination->id,
    ]);

    $destination->delete();

    expect($branch->fresh()->destination_scene_id)->toBeNull();
});

test('scene has incoming branches', function () {
    $destination = Scene::factory()->for($this->session)->create(['sort_order' => 2]);

    $this->scene->branchOptions()->create([
        'label' => 'Path A',
        'game_session_id' => $this->session->id,
        'destination_scene_id' => $destination->id,
    ]);

    $this->scene->branchOptions()->create([
        'label' => 'Path B',
        'game_session_id' => $this->session->id,
        'destination_scene_id' => $destination->id,
    ]);

    expect($destination->incomingBranches)->toHaveCount(2);
});
