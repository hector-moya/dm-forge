<?php

use App\Models\CustomMonster;
use App\Models\Encounter;
use App\Models\GameSession;
use App\Models\SrdMonster;
use App\Models\User;
use Livewire\Livewire;

test('custom monster belongs to user', function () {
    $user = User::factory()->create();
    $monster = CustomMonster::factory()->for($user)->create();

    expect($monster->user->id)->toBe($user->id);
});

test('user can have many custom monsters', function () {
    $user = User::factory()->create();
    CustomMonster::factory()->for($user)->count(3)->create();

    expect($user->customMonsters)->toHaveCount(3);
});

test('custom monster search scope filters by name', function () {
    $user = User::factory()->create();
    CustomMonster::factory()->for($user)->create(['name' => 'Shadow Drake']);
    CustomMonster::factory()->for($user)->create(['name' => 'Fire Elemental']);
    CustomMonster::factory()->for($user)->create(['name' => 'Shadow Wraith']);

    $results = CustomMonster::query()->search('Shadow')->get();

    expect($results)->toHaveCount(2);
});

test('encounter monster links to srd monster', function () {
    $srdMonster = SrdMonster::query()->create([
        'index' => 'goblin',
        'name' => 'Goblin',
        'armor_class' => 15,
        'hit_points' => 7,
        'challenge_rating' => 0.25,
        'xp' => 50,
    ]);

    $session = GameSession::factory()->create();
    $encounter = Encounter::factory()->for($session)->create();

    $encounterMonster = $encounter->monsters()->create([
        'name' => 'Goblin',
        'hp_max' => 7,
        'hp_current' => 7,
        'armor_class' => 15,
        'srd_monster_id' => $srdMonster->id,
        'challenge_rating' => 0.25,
        'xp' => 50,
    ]);

    expect($encounterMonster->srdMonster->name)->toBe('Goblin')
        ->and($encounterMonster->challenge_rating)->toBe(0.25)
        ->and($encounterMonster->xp)->toBe(50);
});

test('encounter monster links to custom monster', function () {
    $user = User::factory()->create();
    $customMonster = CustomMonster::factory()->for($user)->create([
        'name' => 'Shadow Drake',
        'challenge_rating' => 5.0,
        'xp' => 1800,
    ]);

    $session = GameSession::factory()->create();
    $encounter = Encounter::factory()->for($session)->create();

    $encounterMonster = $encounter->monsters()->create([
        'name' => 'Shadow Drake',
        'hp_max' => 80,
        'hp_current' => 80,
        'armor_class' => 16,
        'custom_monster_id' => $customMonster->id,
        'challenge_rating' => 5.0,
        'xp' => 1800,
    ]);

    expect($encounterMonster->customMonster->name)->toBe('Shadow Drake');
});

test('monster form auto-persists manual monsters to custom_monsters', function () {
    $user = User::factory()->create();
    $session = GameSession::factory()->create();
    $encounter = Encounter::factory()->for($session)->create();

    Livewire::actingAs($user)
        ->test('sessions.monster-form', ['encounterId' => $encounter->id])
        ->set('monsterName', 'Homebrew Beast')
        ->set('monsterHpMax', 50)
        ->set('monsterAc', 14)
        ->set('monsterCr', 3.0)
        ->set('monsterXp', 700)
        ->set('monsterCount', 1)
        ->call('save');

    $encounterMonster = $encounter->monsters()->first();
    expect($encounterMonster)->not->toBeNull()
        ->and($encounterMonster->custom_monster_id)->not->toBeNull()
        ->and($encounterMonster->name)->toBe('Homebrew Beast');

    $customMonster = CustomMonster::query()->find($encounterMonster->custom_monster_id);
    expect($customMonster)->not->toBeNull()
        ->and($customMonster->name)->toBe('Homebrew Beast')
        ->and($customMonster->hit_points)->toBe(50)
        ->and($customMonster->armor_class)->toBe(14)
        ->and($customMonster->user_id)->toBe($user->id);
});

test('monster form stores stats from srd monster source', function () {
    $user = User::factory()->create();
    $srdMonster = SrdMonster::query()->create([
        'index' => 'goblin',
        'name' => 'Goblin',
        'armor_class' => 15,
        'hit_points' => 7,
        'challenge_rating' => 0.25,
        'xp' => 50,
        'strength' => 8,
        'dexterity' => 14,
        'constitution' => 10,
        'intelligence' => 10,
        'wisdom' => 8,
        'charisma' => 8,
    ]);

    $session = GameSession::factory()->create();
    $encounter = Encounter::factory()->for($session)->create();

    Livewire::actingAs($user)
        ->test('sessions.monster-form', ['encounterId' => $encounter->id])
        ->call('selectSrdMonster', $srdMonster->id)
        ->call('save');

    $encounterMonster = $encounter->monsters()->first();
    expect($encounterMonster->srd_monster_id)->toBe($srdMonster->id)
        ->and($encounterMonster->stats)->toBeArray()
        ->and($encounterMonster->stats['strength'])->toBe(8)
        ->and($encounterMonster->stats['dexterity'])->toBe(14);
});

test('encounter monster can have loot attached', function () {
    $session = GameSession::factory()->create();
    $encounter = Encounter::factory()->for($session)->create();
    $encounterMonster = $encounter->monsters()->create([
        'name' => 'Goblin Chief',
        'hp_max' => 30,
        'hp_current' => 30,
        'armor_class' => 16,
    ]);

    $equipment = \App\Models\SrdEquipment::query()->create([
        'index' => 'longsword',
        'name' => 'Longsword',
        'equipment_category' => 'Weapon',
    ]);

    $encounterMonster->loot()->create([
        'lootable_type' => \App\Models\SrdEquipment::class,
        'lootable_id' => $equipment->id,
        'quantity' => 1,
        'notes' => 'Magical longsword',
    ]);

    expect($encounterMonster->loot)->toHaveCount(1)
        ->and($encounterMonster->loot->first()->lootable->name)->toBe('Longsword')
        ->and($encounterMonster->loot->first()->notes)->toBe('Magical longsword');
});

test('deleting encounter monster cascades to its loot', function () {
    $session = GameSession::factory()->create();
    $encounter = Encounter::factory()->for($session)->create();
    $encounterMonster = $encounter->monsters()->create([
        'name' => 'Goblin',
        'hp_max' => 7,
        'hp_current' => 7,
        'armor_class' => 15,
    ]);

    $loot = \App\Models\CustomLoot::factory()->create();
    $encounterMonster->loot()->create([
        'lootable_type' => \App\Models\CustomLoot::class,
        'lootable_id' => $loot->id,
        'quantity' => 3,
    ]);

    expect(\App\Models\EncounterMonsterLoot::query()->count())->toBe(1);

    $encounterMonster->delete();

    expect(\App\Models\EncounterMonsterLoot::query()->count())->toBe(0);
});

test('deleting user cascades to custom monsters', function () {
    $user = User::factory()->create();
    CustomMonster::factory()->for($user)->count(3)->create();

    $user->delete();

    expect(CustomMonster::query()->count())->toBe(0);
});
