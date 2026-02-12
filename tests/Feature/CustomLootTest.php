<?php

use App\Models\CustomLoot;
use App\Models\Encounter;
use App\Models\EncounterLoot;
use App\Models\GameSession;
use App\Models\Scene;
use App\Models\SceneLoot;
use App\Models\SrdEquipment;
use App\Models\SrdMagicItem;
use App\Models\User;

test('custom loot belongs to user', function () {
    $user = User::factory()->create();
    $loot = CustomLoot::factory()->for($user)->create();

    expect($loot->user->id)->toBe($user->id);
});

test('encounter can have loot from srd equipment', function () {
    $equipment = SrdEquipment::query()->create([
        'index' => 'longsword',
        'name' => 'Longsword',
        'equipment_category' => 'Weapon',
    ]);

    $session = GameSession::factory()->create();
    $encounter = Encounter::factory()->for($session)->create();

    $encounter->loot()->create([
        'lootable_type' => SrdEquipment::class,
        'lootable_id' => $equipment->id,
        'quantity' => 2,
        'notes' => 'Found on the bandit leader',
    ]);

    $loot = $encounter->loot()->with('lootable')->first();

    expect($loot->lootable->name)->toBe('Longsword')
        ->and($loot->quantity)->toBe(2)
        ->and($loot->notes)->toBe('Found on the bandit leader');
});

test('encounter can have loot from srd magic items', function () {
    $magicItem = SrdMagicItem::query()->create([
        'index' => 'bag-of-holding',
        'name' => 'Bag of Holding',
        'equipment_category' => 'Wondrous Items',
        'rarity' => 'Uncommon',
    ]);

    $session = GameSession::factory()->create();
    $encounter = Encounter::factory()->for($session)->create();

    $encounter->loot()->create([
        'lootable_type' => SrdMagicItem::class,
        'lootable_id' => $magicItem->id,
        'quantity' => 1,
    ]);

    $loot = $encounter->loot()->with('lootable')->first();

    expect($loot->lootable->name)->toBe('Bag of Holding');
});

test('encounter can have custom loot', function () {
    $user = User::factory()->create();
    $customLoot = CustomLoot::factory()->for($user)->create(['name' => 'Dragon Scale Shield']);

    $session = GameSession::factory()->create();
    $encounter = Encounter::factory()->for($session)->create();

    $encounter->loot()->create([
        'lootable_type' => CustomLoot::class,
        'lootable_id' => $customLoot->id,
        'quantity' => 1,
    ]);

    $loot = $encounter->loot()->with('lootable')->first();

    expect($loot->lootable->name)->toBe('Dragon Scale Shield');
});

test('scene can have loot', function () {
    $equipment = SrdEquipment::query()->create([
        'index' => 'potion-of-healing',
        'name' => 'Potion of Healing',
        'equipment_category' => 'Adventuring Gear',
    ]);

    $session = GameSession::factory()->create();
    $scene = Scene::factory()->for($session)->create();

    $scene->loot()->create([
        'lootable_type' => SrdEquipment::class,
        'lootable_id' => $equipment->id,
        'quantity' => 3,
        'notes' => 'Hidden in the chest',
    ]);

    $loot = $scene->loot()->with('lootable')->first();

    expect($loot->lootable->name)->toBe('Potion of Healing')
        ->and($loot->quantity)->toBe(3);
});

test('deleting encounter cascades to encounter loot', function () {
    $equipment = SrdEquipment::query()->create([
        'index' => 'shield',
        'name' => 'Shield',
        'equipment_category' => 'Armor',
    ]);

    $session = GameSession::factory()->create();
    $encounter = Encounter::factory()->for($session)->create();

    $encounter->loot()->create([
        'lootable_type' => SrdEquipment::class,
        'lootable_id' => $equipment->id,
    ]);

    $encounter->delete();

    expect(EncounterLoot::query()->count())->toBe(0);
});

test('deleting scene cascades to scene loot', function () {
    $equipment = SrdEquipment::query()->create([
        'index' => 'rope',
        'name' => 'Rope',
        'equipment_category' => 'Adventuring Gear',
    ]);

    $session = GameSession::factory()->create();
    $scene = Scene::factory()->for($session)->create();

    $scene->loot()->create([
        'lootable_type' => SrdEquipment::class,
        'lootable_id' => $equipment->id,
    ]);

    $scene->delete();

    expect(SceneLoot::query()->count())->toBe(0);
});

test('custom loot search scope filters by name', function () {
    $user = User::factory()->create();
    CustomLoot::factory()->for($user)->create(['name' => 'Crystal Amulet']);
    CustomLoot::factory()->for($user)->create(['name' => 'Crystal Sword']);
    CustomLoot::factory()->for($user)->create(['name' => 'Iron Shield']);

    $results = CustomLoot::query()->search('Crystal')->get();

    expect($results)->toHaveCount(2);
});
