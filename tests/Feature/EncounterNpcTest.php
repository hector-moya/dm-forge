<?php

use App\Models\Campaign;
use App\Models\Encounter;
use App\Models\EncounterNpc;
use App\Models\GameSession;
use App\Models\Npc;
use App\Models\User;

test('encounter npc belongs to encounter', function () {
    $encounterNpc = EncounterNpc::factory()->create();

    expect($encounterNpc->encounter)->toBeInstanceOf(Encounter::class);
});

test('encounter npc belongs to npc', function () {
    $encounterNpc = EncounterNpc::factory()->create();

    expect($encounterNpc->npc)->toBeInstanceOf(Npc::class);
});

test('encounter npc can have null npc_id', function () {
    $encounter = Encounter::factory()->create();

    $encounterNpc = $encounter->npcs()->create([
        'name' => 'Mysterious Stranger',
        'hp_max' => 20,
        'armor_class' => 12,
    ]);

    expect($encounterNpc->npc_id)->toBeNull()
        ->and($encounterNpc->name)->toBe('Mysterious Stranger');
});

test('encounter npcs are deleted when encounter is deleted', function () {
    $encounter = Encounter::factory()->create();

    $encounter->npcs()->create([
        'name' => 'Guard Captain',
        'hp_max' => 30,
        'armor_class' => 16,
    ]);

    expect(EncounterNpc::count())->toBe(1);

    $encounter->delete();

    expect(EncounterNpc::count())->toBe(0);
});

test('encounter npc npc_id is nullified when npc is deleted', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();
    $npc = Npc::factory()->for($campaign)->create(['name' => 'Gandalf']);

    $encounterNpc = $encounter->npcs()->create([
        'npc_id' => $npc->id,
        'name' => 'Gandalf',
        'hp_max' => 50,
        'armor_class' => 15,
    ]);

    $npc->delete();

    expect($encounterNpc->fresh()->npc_id)->toBeNull()
        ->and($encounterNpc->fresh()->name)->toBe('Gandalf');
});

test('encounter npc casts attributes correctly', function () {
    $encounter = Encounter::factory()->create();

    $encounterNpc = $encounter->npcs()->create([
        'name' => 'Test NPC',
        'hp_max' => 25,
        'hp_current' => 20,
        'armor_class' => 14,
        'initiative' => 12,
        'stats' => ['strength' => 16, 'dexterity' => 12],
        'conditions' => ['poisoned', 'prone'],
        'sort_order' => 1,
    ]);

    $encounterNpc->refresh();

    expect($encounterNpc->hp_max)->toBeInt()
        ->and($encounterNpc->hp_current)->toBeInt()
        ->and($encounterNpc->armor_class)->toBeInt()
        ->and($encounterNpc->initiative)->toBeInt()
        ->and($encounterNpc->stats)->toBeArray()
        ->and($encounterNpc->conditions)->toBeArray()
        ->and($encounterNpc->sort_order)->toBeInt();
});

test('encounter has npcs relationship', function () {
    $encounter = Encounter::factory()->create();

    $encounter->npcs()->create([
        'name' => 'NPC A',
        'hp_max' => 20,
        'armor_class' => 12,
    ]);

    $encounter->npcs()->create([
        'name' => 'NPC B',
        'hp_max' => 30,
        'armor_class' => 14,
    ]);

    expect($encounter->npcs)->toHaveCount(2);
});
