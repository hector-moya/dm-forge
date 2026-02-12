<?php

use App\Models\CustomMonster;
use App\Models\Encounter;
use App\Models\GameSession;
use App\Models\SrdMonster;
use App\Models\User;

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

test('deleting user cascades to custom monsters', function () {
    $user = User::factory()->create();
    CustomMonster::factory()->for($user)->count(3)->create();

    $user->delete();

    expect(CustomMonster::query()->count())->toBe(0);
});
