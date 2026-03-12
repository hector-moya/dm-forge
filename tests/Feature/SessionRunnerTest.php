<?php

use App\Models\Campaign;
use App\Models\Character;
use App\Models\Encounter;
use App\Models\GameSession;
use App\Models\Npc;
use App\Models\Scene;
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
        ->test('pages::sessions.runner', ['session' => $session])
        ->call('addCharacterToCombat', $character->id)
        ->assertSet('combatants.0.name', $character->name);
});

test('users can write session notes', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();

    Livewire::actingAs($user)
        ->test('pages::sessions.runner', ['session' => $session])
        ->set('noteEntry', 'The party enters the dungeon')
        ->set('noteType', 'narrative')
        ->call('saveNote');

    expect($session->sessionLogs()->where('entry', 'The party enters the dungeon')->exists())->toBeTrue();
});

test('users can adjust combatant HP', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();
    $character = Character::factory()->for($campaign)->create(['hp_max' => 50, 'hp_current' => 50]);

    $component = Livewire::actingAs($user)
        ->test('pages::sessions.runner', ['session' => $session])
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
        ->test('pages::sessions.runner', ['session' => $session])
        ->call('addCharacterToCombat', $character->id)
        ->call('adjustHp', 0, -100);

    expect($component->get('combatants.0.hp_current'))->toBe(0);
});

test('users can end a session', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();

    Livewire::actingAs($user)
        ->test('pages::sessions.runner', ['session' => $session])
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
        ->test('pages::sessions.runner', ['session' => $session])
        ->call('toggleSceneReveal', $scene->id);

    expect($scene->fresh()->is_revealed)->toBeTrue();
});

test('current scene initializes to first scene by sort order', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();

    $scene2 = $session->scenes()->create(['title' => 'Scene Two', 'sort_order' => 2]);
    $scene1 = $session->scenes()->create(['title' => 'Scene One', 'sort_order' => 1]);

    $component = Livewire::actingAs($user)
        ->test('pages::sessions.runner', ['session' => $session]);

    expect($component->get('currentSceneId'))->toBe($scene1->id);
});

test('current scene resumes from saved current_scene_id', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();

    $scene1 = $session->scenes()->create(['title' => 'Scene One', 'sort_order' => 1]);
    $scene2 = $session->scenes()->create(['title' => 'Scene Two', 'sort_order' => 2]);

    $session->update(['current_scene_id' => $scene2->id]);

    $component = Livewire::actingAs($user)
        ->test('pages::sessions.runner', ['session' => $session]);

    expect($component->get('currentSceneId'))->toBe($scene2->id);
});

test('navigate to scene updates current scene and database', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();

    $scene1 = $session->scenes()->create(['title' => 'Scene One', 'sort_order' => 1]);
    $scene2 = $session->scenes()->create(['title' => 'Scene Two', 'sort_order' => 2, 'is_revealed' => false]);

    $component = Livewire::actingAs($user)
        ->test('pages::sessions.runner', ['session' => $session])
        ->call('navigateToScene', $scene2->id);

    expect($component->get('currentSceneId'))->toBe($scene2->id)
        ->and($session->fresh()->current_scene_id)->toBe($scene2->id)
        ->and($scene2->fresh()->is_revealed)->toBeTrue();
});

test('next scene navigates to next scene by sort order', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();

    $scene1 = $session->scenes()->create(['title' => 'Scene One', 'sort_order' => 1]);
    $scene2 = $session->scenes()->create(['title' => 'Scene Two', 'sort_order' => 2]);
    $scene3 = $session->scenes()->create(['title' => 'Scene Three', 'sort_order' => 3]);

    $component = Livewire::actingAs($user)
        ->test('pages::sessions.runner', ['session' => $session]);

    expect($component->get('currentSceneId'))->toBe($scene1->id);

    $component->call('nextScene');
    expect($component->get('currentSceneId'))->toBe($scene2->id);

    $component->call('nextScene');
    expect($component->get('currentSceneId'))->toBe($scene3->id);

    // No next scene — stays on current
    $component->call('nextScene');
    expect($component->get('currentSceneId'))->toBe($scene3->id);
});

test('previous scene navigates to previous scene by sort order', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();

    $scene1 = $session->scenes()->create(['title' => 'Scene One', 'sort_order' => 1]);
    $scene2 = $session->scenes()->create(['title' => 'Scene Two', 'sort_order' => 2]);

    $component = Livewire::actingAs($user)
        ->test('pages::sessions.runner', ['session' => $session])
        ->call('navigateToScene', $scene2->id);

    $component->call('previousScene');
    expect($component->get('currentSceneId'))->toBe($scene1->id);

    // No previous — stays on current
    $component->call('previousScene');
    expect($component->get('currentSceneId'))->toBe($scene1->id);
});

test('choose branch with destination navigates to destination scene', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();

    $scene1 = $session->scenes()->create(['title' => 'Scene One', 'sort_order' => 1]);
    $scene2 = $session->scenes()->create(['title' => 'Scene Two', 'sort_order' => 2, 'is_revealed' => false]);

    $branch = $scene1->branchOptions()->create([
        'label' => 'Enter the cave',
        'game_session_id' => $session->id,
        'destination_scene_id' => $scene2->id,
    ]);

    $component = Livewire::actingAs($user)
        ->test('pages::sessions.runner', ['session' => $session])
        ->call('chooseBranch', $branch->id);

    expect($branch->fresh()->chosen)->toBeTrue()
        ->and($component->get('currentSceneId'))->toBe($scene2->id)
        ->and($session->fresh()->current_scene_id)->toBe($scene2->id)
        ->and($scene2->fresh()->is_revealed)->toBeTrue();

    // Decision logged
    expect($session->sessionLogs()->where('type', 'decision')->where('entry', 'like', '%Enter the cave%')->exists())->toBeTrue();
});

test('choose branch without destination stays on current scene', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();

    $scene1 = $session->scenes()->create(['title' => 'Scene One', 'sort_order' => 1]);

    $branch = $scene1->branchOptions()->create([
        'label' => 'Investigate the room',
        'game_session_id' => $session->id,
    ]);

    $component = Livewire::actingAs($user)
        ->test('pages::sessions.runner', ['session' => $session])
        ->call('chooseBranch', $branch->id);

    expect($branch->fresh()->chosen)->toBeTrue()
        ->and($component->get('currentSceneId'))->toBe($scene1->id);
});

test('add monsters to combat also loads encounter npcs', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->running()->create();
    $scene = $session->scenes()->create(['title' => 'Battle Scene', 'sort_order' => 1]);
    $encounter = Encounter::factory()->for($session)->create(['scene_id' => $scene->id]);

    $encounter->monsters()->create([
        'name' => 'Goblin',
        'hp_max' => 7,
        'hp_current' => 7,
        'armor_class' => 15,
    ]);

    $npc = Npc::factory()->for($campaign)->create(['name' => 'Ally Guard']);
    $encounter->npcs()->create([
        'npc_id' => $npc->id,
        'name' => 'Ally Guard',
        'hp_max' => 25,
        'armor_class' => 16,
    ]);

    $component = Livewire::actingAs($user)
        ->test('pages::sessions.runner', ['session' => $session])
        ->call('addMonstersToCombat', $encounter->id);

    $combatants = $component->get('combatants');
    $monsterCombatant = collect($combatants)->where('source_type', 'monster')->first();
    $npcCombatant = collect($combatants)->where('source_type', 'encounter_npc')->first();

    expect($monsterCombatant)->not->toBeNull()
        ->and($monsterCombatant['name'])->toBe('Goblin')
        ->and($npcCombatant)->not->toBeNull()
        ->and($npcCombatant['name'])->toBe('Ally Guard')
        ->and($npcCombatant['hp_max'])->toBe(25);
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
