<?php

use App\Models\Campaign;
use App\Models\Character;
use App\Models\User;
use Livewire\Livewire;

test('character form renders for new character', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('characters.create', $campaign))
        ->assertOk();
});

test('character form renders for existing character', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $character = Character::factory()->for($campaign)->create();

    $this->actingAs($user)
        ->get(route('characters.edit', [$campaign, $character]))
        ->assertOk();
});

test('character can be created with basic fields', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::characters.form', ['campaign' => $campaign])
        ->set('name', 'Aria Stormblade')
        ->set('player_name', 'Alice')
        ->set('characterClass', 'Ranger')
        ->set('level', 5)
        ->set('hp_max', 45)
        ->set('hp_current', 40)
        ->set('armor_class', 15)
        ->call('save');

    $character = $campaign->characters()->where('name', 'Aria Stormblade')->first();
    expect($character)->not->toBeNull();
    expect($character->class)->toBe('Ranger');
    expect($character->level)->toBe(5);
});

test('character can be saved with race and background', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::characters.form', ['campaign' => $campaign])
        ->set('name', 'Theron Ashvale')
        ->set('level', 3)
        ->set('hp_max', 24)
        ->set('armor_class', 14)
        ->set('race', 'Half-Elf')
        ->set('background', 'Outlander')
        ->set('speed', 30)
        ->set('proficiency_bonus', 2)
        ->set('experience_points', 900)
        ->call('save');

    $character = $campaign->characters()->where('name', 'Theron Ashvale')->first();
    expect($character)->not->toBeNull();
    expect($character->race)->toBe('Half-Elf');
    expect($character->background)->toBe('Outlander');
    expect($character->speed)->toBe(30);
    expect($character->proficiency_bonus)->toBe(2);
    expect($character->experience_points)->toBe(900);
});

test('character can be saved with ability scores', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::characters.form', ['campaign' => $campaign])
        ->set('name', 'Kira Ironheart')
        ->set('level', 1)
        ->set('hp_max', 12)
        ->set('armor_class', 18)
        ->set('abilityScores', ['str' => 16, 'dex' => 12, 'con' => 14, 'int' => 10, 'wis' => 13, 'cha' => 8])
        ->set('savingThrowProficiencies', ['str', 'con'])
        ->set('skillProficiencies', 'athletics, intimidation, perception')
        ->call('save');

    $character = $campaign->characters()->where('name', 'Kira Ironheart')->first();
    expect($character)->not->toBeNull();
    expect($character->stats['ability_scores']['str'])->toBe(16);
    expect($character->stats['ability_scores']['cha'])->toBe(8);
    expect($character->stats['saving_throw_proficiencies'])->toContain('str');
    expect($character->stats['skill_proficiencies'])->toContain('athletics');
});

test('character form loads full sheet when editing', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $character = Character::factory()->withFullSheet()->for($campaign)->create([
        'race' => 'Dwarf',
        'background' => 'Soldier',
    ]);

    Livewire::actingAs($user)
        ->test('pages::characters.form', ['campaign' => $campaign, 'character' => $character])
        ->assertSet('race', 'Dwarf')
        ->assertSet('background', 'Soldier')
        ->assertSet('abilityScores', $character->stats['ability_scores'])
        ->assertSet('savingThrowProficiencies', $character->stats['saving_throw_proficiencies'])
        ->assertSet('skillProficiencies', implode(', ', $character->stats['skill_proficiencies']));
});

test('character can be saved with equipment and features', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::characters.form', ['campaign' => $campaign])
        ->set('name', 'Brogan Stonefist')
        ->set('level', 2)
        ->set('hp_max', 20)
        ->set('armor_class', 16)
        ->set('equipment', "Longsword\nShield\nChain Mail")
        ->set('featuresTraits', "Second Wind: Bonus action to regain 1d10+Fighter Level HP.\nAction Surge: Take one additional action once per rest.")
        ->set('otherProficiencies', 'All armor, shields, simple and martial weapons')
        ->set('languages', 'Common, Dwarvish')
        ->call('save');

    $character = $campaign->characters()->where('name', 'Brogan Stonefist')->first();
    expect($character)->not->toBeNull();
    expect($character->stats['equipment'])->toHaveCount(3);
    expect($character->stats['features_traits'])->toHaveCount(2);
    expect($character->stats['features_traits'][0]['name'])->toBe('Second Wind');
    expect($character->stats['other_proficiencies'])->toBe('All armor, shields, simple and martial weapons');
    expect($character->stats['languages'])->toBe('Common, Dwarvish');
});

test('character can be saved with spellcasting', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::characters.form', ['campaign' => $campaign])
        ->set('name', 'Lyra Spellweave')
        ->set('level', 5)
        ->set('hp_max', 28)
        ->set('armor_class', 12)
        ->set('spellcastingAbility', 'int')
        ->set('spellSaveDc', 14)
        ->set('spellAttackBonus', 6)
        ->set('cantrips', "Fire Bolt\nPrestidigitation\nMage Hand")
        ->call('save');

    $character = $campaign->characters()->where('name', 'Lyra Spellweave')->first();
    expect($character)->not->toBeNull();
    expect($character->stats['spells']['spellcasting_ability'])->toBe('int');
    expect($character->stats['spells']['spell_save_dc'])->toBe(14);
    expect($character->stats['spells']['cantrips'])->toContain('Fire Bolt');
});

test('character form validates ability scores range', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::characters.form', ['campaign' => $campaign])
        ->set('name', 'Invalid Character')
        ->set('level', 1)
        ->set('hp_max', 10)
        ->set('armor_class', 10)
        ->set('abilityScores', ['str' => 0, 'dex' => 31, 'con' => 10, 'int' => 10, 'wis' => 10, 'cha' => 10])
        ->call('save')
        ->assertHasErrors(['abilityScores.str', 'abilityScores.dex']);
});

test('character abilityModifier returns correct value', function () {
    $character = Character::factory()->create([
        'stats' => [
            'ability_scores' => ['str' => 16, 'dex' => 8, 'con' => 12, 'int' => 10, 'wis' => 14, 'cha' => 18],
        ],
    ]);

    expect($character->abilityModifier('str'))->toBe(3);   // (16-10)/2 = 3
    expect($character->abilityModifier('dex'))->toBe(-1);  // (8-10)/2 = -1
    expect($character->abilityModifier('cha'))->toBe(4);   // (18-10)/2 = 4
    expect($character->abilityModifier('int'))->toBe(0);   // (10-10)/2 = 0
});

test('npc abilityModifier returns correct value', function () {
    $npc = \App\Models\Npc::factory()->create([
        'stats' => [
            'ability_scores' => ['str' => 18, 'dex' => 10, 'con' => 14, 'int' => 6, 'wis' => 12, 'cha' => 8],
        ],
    ]);

    expect($npc->abilityModifier('str'))->toBe(4);   // (18-10)/2 = 4
    expect($npc->abilityModifier('int'))->toBe(-2);  // (6-10)/2 = -2
    expect($npc->abilityModifier('dex'))->toBe(0);   // (10-10)/2 = 0
});
