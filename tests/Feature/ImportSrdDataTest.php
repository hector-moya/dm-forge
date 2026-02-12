<?php

use App\Models\SrdEquipment;
use App\Models\SrdMagicItem;
use App\Models\SrdMonster;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('it imports monsters from the SRD API', function () {
    Http::fake([
        '*/api/monsters' => Http::response([
            'count' => 1,
            'results' => [
                ['index' => 'goblin', 'name' => 'Goblin', 'url' => '/api/monsters/goblin'],
            ],
        ]),
        '*/api/monsters/goblin' => Http::response([
            'index' => 'goblin',
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
            'senses' => ['darkvision' => '60 ft.', 'passive_perception' => 9],
            'languages' => 'Common, Goblin',
            'challenge_rating' => 0.25,
            'xp' => 50,
            'special_abilities' => [
                ['name' => 'Nimble Escape', 'desc' => 'The goblin can take the Disengage or Hide action as a bonus action.'],
            ],
            'actions' => [
                ['name' => 'Scimitar', 'desc' => 'Melee Weapon Attack: +4 to hit.'],
            ],
            'legendary_actions' => [],
            'reactions' => [],
            'image' => '/api/images/monsters/goblin.png',
        ]),
    ]);

    $this->artisan('srd:import', ['--monsters-only' => true])
        ->assertSuccessful();

    $monster = SrdMonster::query()->where('index', 'goblin')->first();

    expect($monster)
        ->not->toBeNull()
        ->name->toBe('Goblin')
        ->size->toBe('Small')
        ->type->toBe('humanoid')
        ->armor_class->toBe(15)
        ->hit_points->toBe(7)
        ->challenge_rating->toBe(0.25)
        ->xp->toBe(50)
        ->languages->toBe('Common, Goblin')
        ->and($monster->special_abilities)->toHaveCount(1)
        ->and($monster->actions)->toHaveCount(1);
});

test('it imports equipment from the SRD API', function () {
    Http::fake([
        '*/api/equipment' => Http::response([
            'count' => 1,
            'results' => [
                ['index' => 'longsword', 'name' => 'Longsword', 'url' => '/api/equipment/longsword'],
            ],
        ]),
        '*/api/equipment/longsword' => Http::response([
            'index' => 'longsword',
            'name' => 'Longsword',
            'equipment_category' => ['name' => 'Weapon'],
            'weapon_category' => 'Martial',
            'weapon_range' => 'Melee',
            'cost' => ['quantity' => 15, 'unit' => 'gp'],
            'damage' => [
                'damage_dice' => '1d8',
                'damage_type' => ['name' => 'Slashing'],
            ],
            'two_handed_damage' => [
                'damage_dice' => '1d10',
                'damage_type' => ['name' => 'Slashing'],
            ],
            'range' => ['normal' => 5],
            'weight' => 3,
            'properties' => [
                ['name' => 'Versatile', 'index' => 'versatile'],
            ],
            'desc' => [],
            'special' => [],
            'armor_class' => null,
            'armor_category' => null,
        ]),
    ]);

    $this->artisan('srd:import', ['--equipment-only' => true])
        ->assertSuccessful();

    $equipment = SrdEquipment::query()->where('index', 'longsword')->first();

    expect($equipment)
        ->not->toBeNull()
        ->name->toBe('Longsword')
        ->equipment_category->toBe('Weapon')
        ->weapon_category->toBe('Martial')
        ->cost_gp->toBe(15.0)
        ->weight->toBe(3.0)
        ->and($equipment->damage['damage_dice'])->toBe('1d8');
});

test('it imports magic items from the SRD API', function () {
    Http::fake([
        '*/api/magic-items' => Http::response([
            'count' => 1,
            'results' => [
                ['index' => 'bag-of-holding', 'name' => 'Bag of Holding', 'url' => '/api/magic-items/bag-of-holding'],
            ],
        ]),
        '*/api/magic-items/bag-of-holding' => Http::response([
            'index' => 'bag-of-holding',
            'name' => 'Bag of Holding',
            'equipment_category' => ['name' => 'Wondrous Items'],
            'rarity' => ['name' => 'Uncommon'],
            'desc' => [
                'This bag has an interior space considerably larger than its outside dimensions.',
                'The bag can hold up to 500 pounds.',
            ],
            'variant' => false,
            'image' => '/api/images/magic-items/bag-of-holding.png',
        ]),
    ]);

    $this->artisan('srd:import', ['--magic-items-only' => true])
        ->assertSuccessful();

    $item = SrdMagicItem::query()->where('index', 'bag-of-holding')->first();

    expect($item)
        ->not->toBeNull()
        ->name->toBe('Bag of Holding')
        ->rarity->toBe('Uncommon')
        ->variant->toBeFalse()
        ->and($item->description)->toContain('500 pounds');
});

test('fresh flag truncates existing data before import', function () {
    SrdMonster::query()->create([
        'index' => 'old-monster',
        'name' => 'Old Monster',
        'challenge_rating' => 1,
        'xp' => 200,
    ]);

    Http::fake([
        '*/api/monsters' => Http::response(['count' => 0, 'results' => []]),
    ]);

    $this->artisan('srd:import', ['--monsters-only' => true, '--fresh' => true])
        ->assertSuccessful();

    expect(SrdMonster::query()->count())->toBe(0);
});

test('import upserts existing records by index', function () {
    SrdMonster::query()->create([
        'index' => 'goblin',
        'name' => 'Old Goblin',
        'hit_points' => 5,
        'challenge_rating' => 0.25,
        'xp' => 50,
    ]);

    Http::fake([
        '*/api/monsters' => Http::response([
            'count' => 1,
            'results' => [['index' => 'goblin', 'name' => 'Goblin', 'url' => '/api/monsters/goblin']],
        ]),
        '*/api/monsters/goblin' => Http::response([
            'index' => 'goblin',
            'name' => 'Goblin',
            'armor_class' => [['type' => 'armor', 'value' => 15]],
            'hit_points' => 7,
            'hit_dice' => '2d6',
            'speed' => ['walk' => '30 ft.'],
            'strength' => 8, 'dexterity' => 14, 'constitution' => 10,
            'intelligence' => 10, 'wisdom' => 8, 'charisma' => 8,
            'proficiencies' => [], 'damage_vulnerabilities' => [],
            'damage_resistances' => [], 'damage_immunities' => [],
            'condition_immunities' => [], 'senses' => [],
            'languages' => 'Common, Goblin',
            'challenge_rating' => 0.25, 'xp' => 50,
            'special_abilities' => [], 'actions' => [],
            'legendary_actions' => [], 'reactions' => [],
        ]),
    ]);

    $this->artisan('srd:import', ['--monsters-only' => true])
        ->assertSuccessful();

    expect(SrdMonster::query()->count())->toBe(1)
        ->and(SrdMonster::query()->first())
        ->name->toBe('Goblin')
        ->hit_points->toBe(7);
});

test('cost conversion normalizes to gold pieces', function () {
    Http::fake([
        '*/api/equipment' => Http::response([
            'count' => 1,
            'results' => [['index' => 'torch', 'name' => 'Torch', 'url' => '/api/equipment/torch']],
        ]),
        '*/api/equipment/torch' => Http::response([
            'index' => 'torch',
            'name' => 'Torch',
            'equipment_category' => ['name' => 'Adventuring Gear'],
            'cost' => ['quantity' => 1, 'unit' => 'cp'],
            'weight' => 1,
            'desc' => [],
            'special' => [],
            'properties' => [],
            'damage' => null,
            'range' => null,
            'armor_class' => null,
        ]),
    ]);

    $this->artisan('srd:import', ['--equipment-only' => true])
        ->assertSuccessful();

    expect(SrdEquipment::query()->first()->cost_gp)->toBe(0.01);
});
