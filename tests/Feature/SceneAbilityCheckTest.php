<?php

use App\Enums\DndSkill;
use App\Models\Campaign;
use App\Models\GameSession;
use App\Models\Scene;
use App\Models\SceneAbilityCheck;
use App\Models\User;
use Livewire\Livewire;

// ── DndSkill enum ────────────────────────────────────────────────────────────

test('DndSkill enum returns correct label', function () {
    expect(DndSkill::Investigation->label())->toBe('Investigation');
    expect(DndSkill::SleightOfHand->label())->toBe('Sleight of Hand');
    expect(DndSkill::AnimalHandling->label())->toBe('Animal Handling');
});

test('DndSkill enum returns correct governing ability', function () {
    expect(DndSkill::Investigation->ability())->toBe('INT');
    expect(DndSkill::Perception->ability())->toBe('WIS');
    expect(DndSkill::Persuasion->ability())->toBe('CHA');
    expect(DndSkill::Athletics->ability())->toBe('STR');
    expect(DndSkill::Stealth->ability())->toBe('DEX');
    expect(DndSkill::Constitution->ability())->toBe('CON');
});

test('DndSkill byAbility returns only skills for that ability', function () {
    $intSkills = DndSkill::byAbility('INT');
    $labels = array_map(fn ($s) => $s->label(), $intSkills);

    expect($labels)->toContain('Arcana')
        ->toContain('History')
        ->toContain('Investigation')
        ->toContain('Nature')
        ->toContain('Religion');
});

// ── Scene Ability Check CRUD ─────────────────────────────────────────────────

test('DM can add an ability check to a scene', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->prepared()->create();
    Scene::factory()->for($session)->create();

    Livewire::actingAs($user)
        ->test('pages::sessions.runner', ['session' => $session])
        ->call('openAbilityCheckForm')
        ->set('abilityCheckForm.skill', DndSkill::Investigation->value)
        ->set('abilityCheckForm.subject', 'the dusty painting')
        ->set('abilityCheckForm.dc', 12)
        ->set('abilityCheckForm.failureText', 'Just a painting.')
        ->set('abilityCheckForm.successText', 'You notice something odd.')
        ->call('saveAbilityCheck')
        ->assertHasNoErrors();

    expect(SceneAbilityCheck::where('subject', 'the dusty painting')->exists())->toBeTrue();
});

test('DM can add an ability check with super success', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->prepared()->create();
    Scene::factory()->for($session)->create();

    Livewire::actingAs($user)
        ->test('pages::sessions.runner', ['session' => $session])
        ->call('openAbilityCheckForm')
        ->set('abilityCheckForm.skill', DndSkill::Arcana->value)
        ->set('abilityCheckForm.dc', 12)
        ->set('abilityCheckForm.dcSuper', 18)
        ->set('abilityCheckForm.failureText', 'You sense nothing.')
        ->set('abilityCheckForm.successText', 'You sense faint magic.')
        ->set('abilityCheckForm.superSuccessText', 'The rune is a portal trigger.')
        ->call('saveAbilityCheck')
        ->assertHasNoErrors();

    $check = SceneAbilityCheck::where('skill', DndSkill::Arcana->value)->first();

    expect($check)->not->toBeNull()
        ->and($check->dc_super)->toBe(18)
        ->and($check->super_success_text)->toBe('The rune is a portal trigger.');
});

test('DM can edit an ability check', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->prepared()->create();
    $scene = Scene::factory()->for($session)->create();
    $check = SceneAbilityCheck::factory()->for($scene)->create([
        'skill' => DndSkill::Perception->value,
        'dc' => 10,
        'failure_text' => 'Nothing.',
        'success_text' => 'You hear footsteps.',
    ]);

    Livewire::actingAs($user)
        ->test('pages::sessions.runner', ['session' => $session])
        ->call('openAbilityCheckForm', $check->id)
        ->set('abilityCheckForm.dc', 14)
        ->set('abilityCheckForm.successText', 'You hear a whisper.')
        ->call('saveAbilityCheck')
        ->assertHasNoErrors();

    expect($check->fresh()->dc)->toBe(14)
        ->and($check->fresh()->success_text)->toBe('You hear a whisper.');
});

test('DM can delete an ability check', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->prepared()->create();
    $scene = Scene::factory()->for($session)->create();
    $check = SceneAbilityCheck::factory()->for($scene)->create();

    Livewire::actingAs($user)
        ->test('pages::sessions.runner', ['session' => $session])
        ->call('deleteAbilityCheck', $check->id);

    expect(SceneAbilityCheck::find($check->id))->toBeNull();
});

test('unauthorized user cannot add ability check to another users scene', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $campaign = Campaign::factory()->for($owner)->create();
    $session = GameSession::factory()->for($campaign)->prepared()->create();
    Scene::factory()->for($session)->create();

    Livewire::actingAs($attacker)
        ->test('pages::sessions.runner', ['session' => $session])
        ->assertForbidden();
});

test('ability check requires skill, dc, failure and success text', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->prepared()->create();
    Scene::factory()->for($session)->create();

    Livewire::actingAs($user)
        ->test('pages::sessions.runner', ['session' => $session])
        ->call('openAbilityCheckForm')
        ->call('saveAbilityCheck')
        ->assertHasErrors(['abilityCheckForm.skill', 'abilityCheckForm.failureText', 'abilityCheckForm.successText']);
});
