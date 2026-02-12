<?php

use App\Models\SrdEquipment;
use App\Models\SrdMagicItem;
use App\Models\SrdMonster;

test('search scope filters monsters by name', function () {
    SrdMonster::query()->create(['index' => 'goblin', 'name' => 'Goblin', 'challenge_rating' => 0.25, 'xp' => 50]);
    SrdMonster::query()->create(['index' => 'dragon', 'name' => 'Ancient Red Dragon', 'challenge_rating' => 24, 'xp' => 62000]);
    SrdMonster::query()->create(['index' => 'hobgoblin', 'name' => 'Hobgoblin', 'challenge_rating' => 0.5, 'xp' => 100]);

    $results = SrdMonster::query()->search('goblin')->get();

    expect($results)->toHaveCount(2)
        ->and($results->pluck('name')->toArray())->toContain('Goblin', 'Hobgoblin');
});

test('challenge rating scope filters monsters by CR', function () {
    SrdMonster::query()->create(['index' => 'goblin', 'name' => 'Goblin', 'challenge_rating' => 0.25, 'xp' => 50]);
    SrdMonster::query()->create(['index' => 'orc', 'name' => 'Orc', 'challenge_rating' => 0.5, 'xp' => 100]);

    $results = SrdMonster::query()->byChallengeRating(0.25)->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('Goblin');
});

test('type scope filters monsters by type', function () {
    SrdMonster::query()->create(['index' => 'goblin', 'name' => 'Goblin', 'type' => 'humanoid', 'challenge_rating' => 0.25, 'xp' => 50]);
    SrdMonster::query()->create(['index' => 'dragon', 'name' => 'Dragon', 'type' => 'dragon', 'challenge_rating' => 24, 'xp' => 62000]);

    $results = SrdMonster::query()->byType('dragon')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('Dragon');
});

test('equipment search scope filters by name', function () {
    SrdEquipment::query()->create(['index' => 'longsword', 'name' => 'Longsword', 'equipment_category' => 'Weapon']);
    SrdEquipment::query()->create(['index' => 'shortsword', 'name' => 'Shortsword', 'equipment_category' => 'Weapon']);
    SrdEquipment::query()->create(['index' => 'shield', 'name' => 'Shield', 'equipment_category' => 'Armor']);

    $results = SrdEquipment::query()->search('sword')->get();

    expect($results)->toHaveCount(2);
});

test('magic item rarity scope filters by rarity', function () {
    SrdMagicItem::query()->create(['index' => 'bag-of-holding', 'name' => 'Bag of Holding', 'equipment_category' => 'Wondrous Items', 'rarity' => 'Uncommon']);
    SrdMagicItem::query()->create(['index' => 'vorpal-sword', 'name' => 'Vorpal Sword', 'equipment_category' => 'Weapon', 'rarity' => 'Legendary']);

    $results = SrdMagicItem::query()->byRarity('Legendary')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('Vorpal Sword');
});

test('monster json fields are properly cast', function () {
    $monster = SrdMonster::query()->create([
        'index' => 'test-monster',
        'name' => 'Test Monster',
        'challenge_rating' => 1,
        'xp' => 200,
        'speed' => ['walk' => '30 ft.', 'fly' => '60 ft.'],
        'actions' => [['name' => 'Bite', 'desc' => 'Melee attack']],
        'senses' => ['darkvision' => '60 ft.'],
    ]);

    $monster->refresh();

    expect($monster->speed)->toBeArray()
        ->and($monster->speed['walk'])->toBe('30 ft.')
        ->and($monster->actions)->toBeArray()
        ->and($monster->actions[0]['name'])->toBe('Bite')
        ->and($monster->senses)->toBeArray();
});
