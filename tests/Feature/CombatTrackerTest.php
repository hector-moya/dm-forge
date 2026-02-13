<?php

use App\Models\Campaign;
use App\Models\Character;
use App\Models\CustomMonster;
use App\Models\Encounter;
use App\Models\GameSession;
use App\Models\Npc;
use App\Models\SrdMonster;
use App\Models\User;
use Livewire\Livewire;

test('combat tracker mounts and loads encounter monsters', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $encounter->monsters()->create([
        'name' => 'Goblin',
        'hp_max' => 7,
        'hp_current' => 7,
        'armor_class' => 15,
    ]);

    Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter])
        ->assertOk()
        ->assertSee('Goblin');
});

test('combat tracker loads campaign characters on mount', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    Character::factory()->for($campaign)->create(['name' => 'Aragorn', 'hp_max' => 100, 'hp_current' => 100, 'armor_class' => 18]);

    Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter])
        ->assertSee('Aragorn');
});

test('add character to combat', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $character = Character::factory()->for($campaign)->create(['name' => 'Legolas']);

    $component = Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter]);

    // Character is auto-loaded on mount, verify it exists
    $combatants = $component->get('combatants');
    expect(collect($combatants)->where('name', 'Legolas')->first())->not->toBeNull();
});

test('add character to combat prevents duplicates', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $character = Character::factory()->for($campaign)->create(['name' => 'Legolas']);

    $component = Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter])
        ->call('addCharacterToCombat', $character->id);

    $combatants = $component->get('combatants');
    $matches = collect($combatants)->where('name', 'Legolas');
    expect($matches)->toHaveCount(1);
});

test('add npc to combat', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $npc = Npc::factory()->for($campaign)->create(['name' => 'Gandalf']);

    Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter])
        ->call('addNpcToCombat', $npc->id)
        ->assertOk();

    // Verify NPC is in combatants
    $component = Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter])
        ->call('addNpcToCombat', $npc->id);

    $combatants = $component->get('combatants');
    expect(collect($combatants)->where('name', 'Gandalf')->first())->not->toBeNull();
});

test('add custom combatant', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $component = Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter])
        ->set('combatantName', 'Dire Wolf')
        ->set('combatantInitiative', 15)
        ->set('combatantHpMax', 37)
        ->set('combatantAc', 14)
        ->call('addCustomCombatant');

    $combatants = $component->get('combatants');
    $wolf = collect($combatants)->where('name', 'Dire Wolf')->first();
    expect($wolf)->not->toBeNull()
        ->and($wolf['hp_max'])->toBe(37)
        ->and($wolf['armor_class'])->toBe(14);
});

test('set initiative auto-sorts combatants', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $encounter->monsters()->createMany([
        ['name' => 'Goblin A', 'hp_max' => 7, 'hp_current' => 7, 'armor_class' => 15],
        ['name' => 'Goblin B', 'hp_max' => 7, 'hp_current' => 7, 'armor_class' => 15],
    ]);

    $component = Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter]);

    // Set Goblin B to higher initiative — find its index first
    $combatants = $component->get('combatants');
    $indexB = collect($combatants)->search(fn ($c) => $c['name'] === 'Goblin B');
    $component->call('setInitiative', $indexB, 20);

    // After sort, re-find Goblin A's index and set lower initiative
    $combatants = $component->get('combatants');
    $indexA = collect($combatants)->search(fn ($c) => $c['name'] === 'Goblin A');
    $component->call('setInitiative', $indexA, 5);

    $combatants = $component->get('combatants');
    expect($combatants[0]['name'])->toBe('Goblin B')
        ->and($combatants[0]['initiative'])->toBe(20)
        ->and($combatants[1]['name'])->toBe('Goblin A')
        ->and($combatants[1]['initiative'])->toBe(5);
});

test('hp adjustment clamps between zero and max', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $encounter->monsters()->create([
        'name' => 'Goblin',
        'hp_max' => 7,
        'hp_current' => 7,
        'armor_class' => 15,
    ]);

    $component = Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter])
        ->call('selectCombatant', 0)
        ->call('adjustHp', 0, -5);

    expect($component->get('combatants.0.hp_current'))->toBe(2);

    // Over-heal should clamp to max
    $component->call('adjustHp', 0, 100);
    expect($component->get('combatants.0.hp_current'))->toBe(7);

    // Over-damage should clamp to 0
    $component->call('adjustHp', 0, -100);
    expect($component->get('combatants.0.hp_current'))->toBe(0);
});

test('heal full restores hp to max', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $encounter->monsters()->create([
        'name' => 'Goblin',
        'hp_max' => 7,
        'hp_current' => 7,
        'armor_class' => 15,
    ]);

    $component = Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter])
        ->call('adjustHp', 0, -5)
        ->call('healFull', 0);

    expect($component->get('combatants.0.hp_current'))->toBe(7);
});

test('condition toggle adds and removes conditions', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $encounter->monsters()->create([
        'name' => 'Goblin',
        'hp_max' => 7,
        'hp_current' => 7,
        'armor_class' => 15,
    ]);

    $component = Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter])
        ->call('toggleCondition', 0, 'poisoned');

    expect($component->get('combatants.0.conditions'))->toContain('poisoned');

    // Toggle off
    $component->call('toggleCondition', 0, 'poisoned');
    expect($component->get('combatants.0.conditions'))->not->toContain('poisoned');
});

test('end combat syncs monster data to database', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $monster = $encounter->monsters()->create([
        'name' => 'Goblin',
        'hp_max' => 7,
        'hp_current' => 7,
        'armor_class' => 15,
    ]);

    $component = Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter])
        ->call('startCombat')
        ->call('setInitiative', 0, 18)
        ->call('adjustHp', 0, -3)
        ->call('toggleCondition', 0, 'prone')
        ->call('endCombat');

    $monster->refresh();
    expect($monster->hp_current)->toBe(4)
        ->and($monster->initiative)->toBe(18)
        ->and($monster->conditions)->toContain('prone');
});

test('end combat syncs character hp to database', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $character = Character::factory()->for($campaign)->create([
        'name' => 'Frodo',
        'hp_max' => 30,
        'hp_current' => 30,
        'armor_class' => 12,
    ]);

    // Find character index (auto-loaded on mount)
    $component = Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter]);

    $combatants = $component->get('combatants');
    $charIndex = collect($combatants)->search(fn ($c) => $c['source_type'] === 'character' && $c['source_id'] === $character->id);

    $component->call('startCombat')
        ->call('adjustHp', $charIndex, -10)
        ->call('endCombat');

    $character->refresh();
    expect($character->hp_current)->toBe(20);
});

test('stat block loads for srd monster', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $srdMonster = SrdMonster::query()->create([
        'index' => 'goblin',
        'name' => 'Goblin',
        'size' => 'Small',
        'type' => 'humanoid',
        'alignment' => 'neutral evil',
        'armor_class' => 15,
        'hit_points' => 7,
        'hit_dice' => '2d6',
        'speed' => ['walk' => '30 ft.'],
        'strength' => 8,
        'dexterity' => 14,
        'constitution' => 10,
        'intelligence' => 10,
        'wisdom' => 8,
        'charisma' => 8,
        'challenge_rating' => 0.25,
        'xp' => 50,
        'languages' => 'Common, Goblin',
        'actions' => [['name' => 'Scimitar', 'desc' => 'Melee Attack: +4 to hit']],
    ]);

    $encounter->monsters()->create([
        'name' => 'Goblin',
        'hp_max' => 7,
        'hp_current' => 7,
        'armor_class' => 15,
        'srd_monster_id' => $srdMonster->id,
    ]);

    $component = Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter])
        ->call('selectCombatant', 0);

    // The stat block should be rendered in the view
    $component->assertSee('Small humanoid')
        ->assertSee('neutral evil')
        ->assertSee('Scimitar');
});

test('stat block loads for custom monster', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $customMonster = CustomMonster::factory()->for($user)->create([
        'name' => 'Shadow Drake',
        'size' => 'Large',
        'type' => 'dragon',
        'alignment' => 'chaotic evil',
        'armor_class' => 16,
        'hit_points' => 85,
        'challenge_rating' => 5.0,
        'xp' => 1800,
        'strength' => 18,
        'actions' => [['name' => 'Bite', 'desc' => 'Melee Attack: +7 to hit']],
    ]);

    $encounter->monsters()->create([
        'name' => 'Shadow Drake',
        'hp_max' => 85,
        'hp_current' => 85,
        'armor_class' => 16,
        'custom_monster_id' => $customMonster->id,
    ]);

    $component = Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter])
        ->call('selectCombatant', 0);

    $component->assertSee('Large dragon')
        ->assertSee('chaotic evil')
        ->assertSee('Bite');
});

test('stat block loads for character', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $character = Character::factory()->for($campaign)->create([
        'name' => 'Gandalf',
        'player_name' => 'Ian',
        'class' => 'Wizard',
        'level' => 20,
        'stats' => ['strength' => 10, 'dexterity' => 14, 'constitution' => 12, 'intelligence' => 20, 'wisdom' => 18, 'charisma' => 16],
    ]);

    $component = Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter])
        ->call('selectCombatant', 0);

    $component->assertSee('Gandalf')
        ->assertSee('Wizard')
        ->assertSee('Ian');
});

test('combat tracker denies access to other users', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $campaign = Campaign::factory()->for($owner)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    Livewire::actingAs($other)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter])
        ->assertForbidden();
});

test('remove combatant works correctly', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $encounter->monsters()->createMany([
        ['name' => 'Goblin A', 'hp_max' => 7, 'hp_current' => 7, 'armor_class' => 15],
        ['name' => 'Goblin B', 'hp_max' => 7, 'hp_current' => 7, 'armor_class' => 15],
    ]);

    $component = Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter])
        ->call('removeCombatant', 0);

    $combatants = $component->get('combatants');
    expect($combatants)->toHaveCount(1)
        ->and($combatants[0]['name'])->toBe('Goblin B');
});

test('next and previous turn cycle through combatants', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $encounter->monsters()->createMany([
        ['name' => 'Goblin A', 'hp_max' => 7, 'hp_current' => 7, 'armor_class' => 15],
        ['name' => 'Goblin B', 'hp_max' => 7, 'hp_current' => 7, 'armor_class' => 15],
    ]);

    $component = Livewire::actingAs($user)
        ->test('sessions.combat-tracker', ['session' => $session, 'encounter' => $encounter])
        ->call('startCombat');

    expect($component->get('currentTurnIndex'))->toBe(0);

    $component->call('nextTurn');
    expect($component->get('currentTurnIndex'))->toBe(1);

    $component->call('nextTurn');
    expect($component->get('currentTurnIndex'))->toBe(0); // wraps around

    $component->call('previousTurn');
    expect($component->get('currentTurnIndex'))->toBe(1); // wraps backwards
});
