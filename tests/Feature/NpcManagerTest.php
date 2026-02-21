<?php

use App\Ai\Agents\ImagePromptCrafter;
use App\Ai\Agents\NpcGenerator;
use App\Models\Campaign;
use App\Models\Faction;
use App\Models\Location;
use App\Models\Npc;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Image;
use Livewire\Livewire;

test('npc manager page loads for campaign owner', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('campaigns.npcs', $campaign))
        ->assertOk();
});

test('npc manager denies access to non-owner', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $campaign = Campaign::factory()->for($other)->create();

    $this->actingAs($user)
        ->get(route('campaigns.npcs', $campaign))
        ->assertForbidden();
});

test('npc manager lists npcs', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    Npc::factory()->for($campaign)->create(['name' => 'Gornik the Bold']);

    Livewire::actingAs($user)
        ->test('pages::campaigns.npc-manager', ['campaign' => $campaign])
        ->assertSee('Gornik the Bold');
});

test('npc manager can create an npc', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.npc-manager', ['campaign' => $campaign])
        ->call('openForm')
        ->assertSet('showForm', true)
        ->set('npcName', 'Elena Darkwood')
        ->set('npcRole', 'Ranger')
        ->set('npcDescription', 'A skilled ranger from the north')
        ->call('save');

    $npc = $campaign->npcs()->where('name', 'Elena Darkwood')->first();
    expect($npc)->not->toBeNull();
    expect($npc->role)->toBe('Ranger');
});

test('npc manager can create npc with faction and location', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $faction = Faction::factory()->for($campaign)->create();
    $location = Location::factory()->for($campaign)->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.npc-manager', ['campaign' => $campaign])
        ->call('openForm')
        ->set('npcName', 'Guard Captain')
        ->set('npcFactionId', $faction->id)
        ->set('npcLocationId', $location->id)
        ->call('save');

    $npc = $campaign->npcs()->where('name', 'Guard Captain')->first();
    expect($npc->faction_id)->toBe($faction->id);
    expect($npc->location_id)->toBe($location->id);
});

test('npc manager can edit an npc', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $npc = Npc::factory()->for($campaign)->create(['name' => 'Old Name', 'role' => 'Guard']);

    Livewire::actingAs($user)
        ->test('pages::campaigns.npc-manager', ['campaign' => $campaign])
        ->call('openForm', $npc->id)
        ->assertSet('npcName', 'Old Name')
        ->assertSet('npcRole', 'Guard')
        ->set('npcName', 'New Name')
        ->call('save');

    expect($npc->fresh()->name)->toBe('New Name');
});

test('npc manager can delete an npc', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $npc = Npc::factory()->for($campaign)->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.npc-manager', ['campaign' => $campaign])
        ->call('delete', $npc->id);

    expect($campaign->npcs()->count())->toBe(0);
});

test('npc manager search filters npcs', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    Npc::factory()->for($campaign)->create(['name' => 'Gornik the Bold']);
    Npc::factory()->for($campaign)->create(['name' => 'Elena Darkwood']);

    Livewire::actingAs($user)
        ->test('pages::campaigns.npc-manager', ['campaign' => $campaign])
        ->set('search', 'Elena')
        ->assertSee('Elena Darkwood')
        ->assertDontSee('Gornik the Bold');
});

test('npc manager faction filter works', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $faction = Faction::factory()->for($campaign)->create();
    Npc::factory()->for($campaign)->create(['name' => 'Member', 'faction_id' => $faction->id]);
    Npc::factory()->for($campaign)->create(['name' => 'Loner', 'faction_id' => null]);

    Livewire::actingAs($user)
        ->test('pages::campaigns.npc-manager', ['campaign' => $campaign])
        ->set('factionFilter', $faction->id)
        ->assertSee('Member')
        ->assertDontSee('Loner');
});

test('npc manager alive filter works', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    Npc::factory()->for($campaign)->create(['name' => 'Living NPC', 'is_alive' => true]);
    Npc::factory()->for($campaign)->create(['name' => 'Dead NPC', 'is_alive' => false]);

    Livewire::actingAs($user)
        ->test('pages::campaigns.npc-manager', ['campaign' => $campaign])
        ->set('aliveFilter', 'dead')
        ->assertSee('Dead NPC')
        ->assertDontSee('Living NPC');
});

test('npc manager saves voice fields and catchphrases', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.npc-manager', ['campaign' => $campaign])
        ->call('openForm')
        ->set('npcName', 'Voiced NPC')
        ->set('npcVoiceDescription', 'Deep, rumbling')
        ->set('npcSpeechPatterns', 'Short sentences')
        ->set('npcCatchphrases', "Halt!\nWho goes there?")
        ->call('save');

    $npc = $campaign->npcs()->where('name', 'Voiced NPC')->first();
    expect($npc->voice_description)->toBe('Deep, rumbling');
    expect($npc->speech_patterns)->toBe('Short sentences');
    expect($npc->catchphrases)->toBe(['Halt!', 'Who goes there?']);
});

test('npc manager shows detail flyout with history', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $npc = Npc::factory()->for($campaign)->create(['name' => 'Test NPC']);

    $campaign->worldEvents()->create([
        'title' => 'NPC betrayed the guild',
        'description' => 'Stole the treasury',
        'event_type' => 'npc_decision',
        'npc_id' => $npc->id,
        'occurred_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test('pages::campaigns.npc-manager', ['campaign' => $campaign])
        ->call('viewNpc', $npc->id)
        ->assertSet('viewingNpcId', $npc->id);
});

test('npc manager generates npc with ai', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    NpcGenerator::fake([
        [
            'name' => 'Gornik the Bold',
            'role' => 'Tavern Owner',
            'description' => 'A burly half-orc',
            'personality' => 'Jovial but quick to anger',
            'motivation' => 'Protect his establishment',
            'voice_description' => 'Deep bass',
            'speech_patterns' => 'Short sentences',
            'catchphrases' => ['Drink up!', 'You break it, you buy it!'],
            'backstory' => 'Former adventurer who settled down',
            'suggested_faction' => null,
            'suggested_location' => null,
        ],
    ]);

    Livewire::actingAs($user)
        ->test('pages::campaigns.npc-manager', ['campaign' => $campaign])
        ->call('openGenerateModal')
        ->assertSet('showGenerateModal', true)
        ->set('generateContext', 'A tavern owner')
        ->call('generate')
        ->assertSet('showGenerateModal', false)
        ->assertSet('showForm', true)
        ->assertSet('npcName', 'Gornik the Bold')
        ->assertSet('npcRole', 'Tavern Owner')
        ->assertSet('npcVoiceDescription', 'Deep bass');

    NpcGenerator::assertPrompted(fn ($prompt) => $prompt->contains('tavern owner'));
});

test('npc manager can generate image for npc', function () {
    Storage::fake('public');
    ImagePromptCrafter::fake();
    Image::fake();

    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $npc = Npc::factory()->for($campaign)->create(['name' => 'Gornik']);

    Livewire::actingAs($user)
        ->test('pages::campaigns.npc-manager', ['campaign' => $campaign])
        ->call('generateImage', $npc->id);

    expect($npc->fresh()->image_path)->not->toBeNull();
    ImagePromptCrafter::assertPrompted(fn ($prompt) => $prompt->contains('npc'));
    Image::assertGenerated(fn () => true);
});

test('npc manager can save npc with stat block fields', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.npc-manager', ['campaign' => $campaign])
        ->call('openForm')
        ->set('npcName', 'Gareth the Guard')
        ->set('npcRace', 'Human')
        ->set('npcSize', 'Medium')
        ->set('npcAlignment', 'Lawful Neutral')
        ->set('npcArmorClass', 16)
        ->set('npcArmorType', 'Chain mail')
        ->set('npcHpMax', 52)
        ->set('npcHitDice', '8d8+16')
        ->set('npcSpeed', '30 ft.')
        ->set('npcChallengeRating', '2')
        ->set('npcAbilityScores', ['str' => 16, 'dex' => 13, 'con' => 14, 'int' => 10, 'wis' => 11, 'cha' => 10])
        ->set('npcSavingThrowProficiencies', ['str', 'con'])
        ->set('npcSkillProficiencies', 'athletics, perception')
        ->set('npcLanguages', 'Common')
        ->set('npcActions', 'Longsword: Melee Weapon Attack: +5 to hit, reach 5 ft., one target.')
        ->call('save');

    $npc = $campaign->npcs()->where('name', 'Gareth the Guard')->first();
    expect($npc)->not->toBeNull();
    expect($npc->race)->toBe('Human');
    expect($npc->size)->toBe('Medium');
    expect($npc->alignment)->toBe('Lawful Neutral');
    expect($npc->armor_class)->toBe(16);
    expect($npc->armor_type)->toBe('Chain mail');
    expect($npc->hp_max)->toBe(52);
    expect($npc->hit_dice)->toBe('8d8+16');
    expect($npc->speed)->toBe('30 ft.');
    expect($npc->challenge_rating)->toBe('2');
    expect($npc->stats['ability_scores']['str'])->toBe(16);
    expect($npc->stats['saving_throw_proficiencies'])->toContain('str');
    expect($npc->stats['skill_proficiencies'])->toContain('athletics');
    expect($npc->stats['actions'])->toHaveCount(1);
    expect($npc->stats['actions'][0]['name'])->toBe('Longsword');
});

test('npc manager loads stat block fields when editing', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $npc = Npc::factory()->withStatBlock()->for($campaign)->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.npc-manager', ['campaign' => $campaign])
        ->call('openForm', $npc->id)
        ->assertSet('npcRace', $npc->race ?? '')
        ->assertSet('npcArmorClass', $npc->armor_class)
        ->assertSet('npcHpMax', $npc->hp_max)
        ->assertSet('npcAbilityScores', $npc->stats['ability_scores']);
});

test('npc manager generate populates stat block form fields from ai', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    NpcGenerator::fake([
        [
            'name' => 'Sylara Moonwhisper',
            'role' => 'Elven Mage',
            'description' => 'A slender elf with silver hair',
            'personality' => 'Aloof and scholarly',
            'motivation' => 'Seeks forbidden arcane knowledge',
            'voice_description' => 'Melodic and precise',
            'speech_patterns' => 'Formal, archaic vocabulary',
            'catchphrases' => ['Knowledge is power.'],
            'backstory' => 'Exiled from the Elven Council',
            'race' => 'Elf',
            'size' => 'Medium',
            'alignment' => 'Chaotic Neutral',
            'armor_class' => 13,
            'armor_type' => 'Mage Armor',
            'hp_max' => 45,
            'hit_dice' => '10d8',
            'speed' => '30 ft.',
            'challenge_rating' => '5',
            'ability_scores' => ['str' => 8, 'dex' => 14, 'con' => 10, 'int' => 18, 'wis' => 12, 'cha' => 14],
            'saving_throw_proficiencies' => ['int', 'wis'],
            'skill_proficiencies' => ['arcana', 'history'],
            'damage_resistances' => [],
            'damage_immunities' => [],
            'condition_immunities' => [],
            'senses' => 'Darkvision 60 ft., passive Perception 11',
            'languages' => 'Common, Elvish, Draconic',
            'special_traits' => [['name' => 'Spellcasting', 'description' => 'Sylara is a 10th-level spellcaster.']],
            'actions' => [['name' => 'Dagger', 'description' => 'Melee or Ranged Weapon Attack: +4 to hit.']],
            'bonus_actions' => [],
            'reactions' => [],
            'legendary_actions' => [],
            'suggested_faction' => null,
            'suggested_location' => null,
        ],
    ]);

    Livewire::actingAs($user)
        ->test('pages::campaigns.npc-manager', ['campaign' => $campaign])
        ->call('openGenerateModal')
        ->call('generate')
        ->assertSet('npcRace', 'Elf')
        ->assertSet('npcArmorClass', 13)
        ->assertSet('npcHpMax', 45)
        ->assertSet('npcChallengeRating', '5')
        ->assertSet('npcAbilityScores', ['str' => 8, 'dex' => 14, 'con' => 10, 'int' => 18, 'wis' => 12, 'cha' => 14])
        ->assertSet('npcSavingThrowProficiencies', ['int', 'wis'])
        ->assertSet('npcSkillProficiencies', 'arcana, history')
        ->assertSet('npcLanguages', 'Common, Elvish, Draconic');
});

test('npc manager handles ai failure gracefully', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    NpcGenerator::fake(function () {
        throw new \RuntimeException('API error');
    });

    Livewire::actingAs($user)
        ->test('pages::campaigns.npc-manager', ['campaign' => $campaign])
        ->call('openGenerateModal')
        ->call('generate')
        ->assertSet('generating', false)
        ->assertSet('showForm', false);
});
