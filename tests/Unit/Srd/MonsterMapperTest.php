<?php

use App\Srd\Mappers\MonsterMapper;

test('monster mapper maps full api response', function () {
    $mapper = new MonsterMapper;

    $result = $mapper->map([
        'name' => 'Goblin',
        'size' => 'Small',
        'type' => 'humanoid',
        'subtype' => 'goblinoid',
        'alignment' => 'neutral evil',
        'armor_class' => [['type' => 'armor', 'value' => 15]],
        'hit_points' => 7,
        'hit_dice' => '2d6',
        'speed' => ['walk' => '30 ft.'],
        'strength' => 8,
        'dexterity' => 14,
        'constitution' => 10,
        'intelligence' => 10,
        'wisdom' => 8,
        'charisma' => 8,
        'proficiencies' => [
            ['proficiency' => ['name' => 'Skill: Stealth'], 'value' => 6],
        ],
        'damage_vulnerabilities' => [],
        'damage_resistances' => [],
        'damage_immunities' => [],
        'condition_immunities' => [],
        'senses' => ['darkvision' => '60 ft.'],
        'languages' => 'Common, Goblin',
        'challenge_rating' => 0.25,
        'xp' => 50,
        'special_abilities' => [['name' => 'Nimble Escape', 'desc' => 'Bonus action.']],
        'actions' => [['name' => 'Scimitar', 'desc' => 'Melee attack.']],
        'legendary_actions' => [],
        'reactions' => [],
        'image' => '/api/images/monsters/goblin.png',
    ]);

    expect($result['name'])->toBe('Goblin')
        ->and($result['armor_class'])->toBe(15)
        ->and($result['armor_class_type'])->toBe('armor')
        ->and($result['hit_points'])->toBe(7)
        ->and($result['challenge_rating'])->toBe(0.25)
        ->and($result['proficiencies'])->toBe([['name' => 'Skill: Stealth', 'value' => 6]])
        ->and($result['special_abilities'])->toBe([['name' => 'Nimble Escape', 'desc' => 'Bonus action.']])
        ->and($result['image_url'])->toBe('/api/images/monsters/goblin.png');
});

test('monster mapper applies defaults for missing fields', function () {
    $mapper = new MonsterMapper;

    $result = $mapper->map(['name' => 'Placeholder']);

    expect($result['armor_class'])->toBe(10)
        ->and($result['hit_points'])->toBe(1)
        ->and($result['strength'])->toBe(10)
        ->and($result['challenge_rating'])->toBe(0)
        ->and($result['xp'])->toBe(0)
        ->and($result['proficiencies'])->toBe([])
        ->and($result['special_abilities'])->toBe([]);
});

test('monster mapper maps condition immunities as name list', function () {
    $mapper = new MonsterMapper;

    $result = $mapper->map([
        'name' => 'Skeleton',
        'condition_immunities' => [
            ['name' => 'Poisoned', 'index' => 'poisoned'],
            ['name' => 'Exhaustion', 'index' => 'exhaustion'],
        ],
    ]);

    expect($result['condition_immunities'])->toBe(['Poisoned', 'Exhaustion']);
});
