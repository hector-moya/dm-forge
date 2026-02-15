<?php

use App\Ai\Agents\MonsterGenerator;
use App\Models\User;
use Livewire\Livewire;

test('monster generator agent implements structured output', function () {
    $agent = new MonsterGenerator;

    expect($agent)->toBeInstanceOf(\Laravel\Ai\Contracts\HasStructuredOutput::class);
    expect($agent->instructions())->toContain('monster');
});

test('monster generator returns structured monster data', function () {
    MonsterGenerator::fake([
        [
            'name' => 'Flamescale Drake',
            'size' => 'Large',
            'type' => 'dragon',
            'subtype' => null,
            'alignment' => 'chaotic evil',
            'armor_class' => 16,
            'armor_class_type' => 'natural armor',
            'hit_points' => 85,
            'hit_dice' => '10d10+30',
            'strength' => 18,
            'dexterity' => 14,
            'constitution' => 16,
            'intelligence' => 8,
            'wisdom' => 12,
            'charisma' => 10,
            'challenge_rating' => 5,
            'xp' => 1800,
            'special_abilities' => [
                ['name' => 'Fire Breath', 'desc' => 'Recharge 5-6. Exhales fire in a 30-foot cone.'],
            ],
            'actions' => [
                ['name' => 'Bite', 'desc' => 'Melee Attack: +7 to hit, 2d10+4 piercing damage.'],
                ['name' => 'Claw', 'desc' => 'Melee Attack: +7 to hit, 2d6+4 slashing damage.'],
            ],
            'languages' => 'Draconic',
            'notes' => 'Found in volcanic regions. Hunts in pairs.',
        ],
    ]);

    $response = (new MonsterGenerator)->prompt('Generate a fire dragon');

    expect($response['name'])->toBe('Flamescale Drake');
    expect($response['size'])->toBe('Large');
    expect($response['type'])->toBe('dragon');
    expect($response['armor_class'])->toBe(16);
    expect($response['hit_points'])->toBe(85);
    expect($response['actions'])->toHaveCount(2);

    MonsterGenerator::assertPrompted(fn ($prompt) => $prompt->contains('fire dragon'));
});

test('monster generator populates monster library form', function () {
    $user = User::factory()->create();

    MonsterGenerator::fake([
        [
            'name' => 'Shadow Lurker',
            'size' => 'Medium',
            'type' => 'undead',
            'subtype' => null,
            'alignment' => 'neutral evil',
            'armor_class' => 13,
            'armor_class_type' => null,
            'hit_points' => 45,
            'hit_dice' => '6d8+18',
            'strength' => 14,
            'dexterity' => 16,
            'constitution' => 16,
            'intelligence' => 6,
            'wisdom' => 10,
            'charisma' => 8,
            'challenge_rating' => 3,
            'xp' => 700,
            'special_abilities' => [],
            'actions' => [
                ['name' => 'Shadow Strike', 'desc' => 'Melee Attack: +5 to hit, 1d8+3 necrotic.'],
            ],
            'languages' => 'understands Common',
            'notes' => 'Lurks in dark corners.',
        ],
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Library\MonsterLibrary::class)
        ->call('openGenerateModal')
        ->assertSet('showGenerateModal', true)
        ->set('generateContext', 'An undead shadow creature')
        ->call('generateMonster')
        ->assertSet('showGenerateModal', false)
        ->assertSet('showCustomForm', true)
        ->assertSet('customName', 'Shadow Lurker')
        ->assertSet('customSize', 'Medium')
        ->assertSet('customType', 'undead')
        ->assertSet('customArmorClass', 13)
        ->assertSet('customHitPoints', 45)
        ->assertSet('customStrength', 14)
        ->assertSet('customDexterity', 16);

    MonsterGenerator::assertPrompted(fn ($prompt) => $prompt->contains('undead shadow'));
});

test('monster library handles ai failure gracefully', function () {
    $user = User::factory()->create();

    MonsterGenerator::fake(function () {
        throw new \RuntimeException('API error');
    });

    Livewire::actingAs($user)
        ->test(\App\Livewire\Library\MonsterLibrary::class)
        ->call('openGenerateModal')
        ->call('generateMonster')
        ->assertSet('generating', false)
        ->assertSet('showCustomForm', false);
});

test('generated monster can be saved as custom monster', function () {
    $user = User::factory()->create();

    MonsterGenerator::fake([
        [
            'name' => 'Frost Elemental',
            'size' => 'Large',
            'type' => 'elemental',
            'subtype' => null,
            'alignment' => 'neutral',
            'armor_class' => 15,
            'armor_class_type' => 'natural armor',
            'hit_points' => 90,
            'hit_dice' => '12d10+24',
            'strength' => 18,
            'dexterity' => 10,
            'constitution' => 14,
            'intelligence' => 6,
            'wisdom' => 10,
            'charisma' => 6,
            'challenge_rating' => 5,
            'xp' => 1800,
            'special_abilities' => [],
            'actions' => [
                ['name' => 'Slam', 'desc' => 'Melee Attack: +7 to hit, 2d8+4 bludgeoning plus 1d6 cold.'],
            ],
            'languages' => 'Primordial',
            'notes' => 'Found in frozen tundra.',
        ],
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Library\MonsterLibrary::class)
        ->call('openGenerateModal')
        ->call('generateMonster')
        ->call('saveCustomMonster');

    expect($user->customMonsters()->where('name', 'Frost Elemental')->exists())->toBeTrue();
    $monster = $user->customMonsters()->where('name', 'Frost Elemental')->first();
    expect($monster->size)->toBe('Large');
    expect($monster->type)->toBe('elemental');
    expect($monster->armor_class)->toBe(15);
});
