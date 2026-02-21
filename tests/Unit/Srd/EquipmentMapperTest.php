<?php

use App\Srd\Mappers\EquipmentMapper;

test('equipment mapper converts gp cost correctly', function () {
    $mapper = new EquipmentMapper;

    $result = $mapper->map([
        'name' => 'Longsword',
        'equipment_category' => ['name' => 'Weapon'],
        'cost' => ['quantity' => 15, 'unit' => 'gp'],
        'properties' => [],
        'desc' => [],
    ]);

    expect($result['cost_gp'])->toBe(15.0);
});

test('equipment mapper converts copper pieces to gp', function () {
    $mapper = new EquipmentMapper;

    $result = $mapper->map([
        'name' => 'Torch',
        'equipment_category' => ['name' => 'Adventuring Gear'],
        'cost' => ['quantity' => 1, 'unit' => 'cp'],
        'properties' => [],
        'desc' => [],
    ]);

    expect($result['cost_gp'])->toBe(0.01);
});

test('equipment mapper converts silver pieces to gp', function () {
    $mapper = new EquipmentMapper;

    $result = $mapper->map([
        'name' => 'Dagger',
        'equipment_category' => ['name' => 'Weapon'],
        'cost' => ['quantity' => 20, 'unit' => 'sp'],
        'properties' => [],
        'desc' => [],
    ]);

    expect($result['cost_gp'])->toBe(2.0);
});

test('equipment mapper joins description paragraphs', function () {
    $mapper = new EquipmentMapper;

    $result = $mapper->map([
        'name' => 'Bag of Tricks',
        'equipment_category' => ['name' => 'Wondrous Item'],
        'desc' => ['First paragraph.', 'Second paragraph.'],
        'properties' => [],
    ]);

    expect($result['description'])->toBe("First paragraph.\n\nSecond paragraph.");
});

test('equipment mapper returns null description for empty desc', function () {
    $mapper = new EquipmentMapper;

    $result = $mapper->map([
        'name' => 'Arrow',
        'equipment_category' => ['name' => 'Ammunition'],
        'desc' => [],
        'properties' => [],
    ]);

    expect($result['description'])->toBeNull();
});

test('equipment mapper maps damage and two handed damage', function () {
    $mapper = new EquipmentMapper;

    $result = $mapper->map([
        'name' => 'Longsword',
        'equipment_category' => ['name' => 'Weapon'],
        'damage' => [
            'damage_dice' => '1d8',
            'damage_type' => ['name' => 'Slashing'],
        ],
        'two_handed_damage' => [
            'damage_dice' => '1d10',
            'damage_type' => ['name' => 'Slashing'],
        ],
        'properties' => [['name' => 'Versatile', 'index' => 'versatile']],
        'desc' => [],
    ]);

    expect($result['damage']['damage_dice'])->toBe('1d8')
        ->and($result['damage']['damage_type'])->toBe('Slashing')
        ->and($result['two_handed_damage']['damage_dice'])->toBe('1d10')
        ->and($result['properties'])->toBe(['Versatile']);
});
