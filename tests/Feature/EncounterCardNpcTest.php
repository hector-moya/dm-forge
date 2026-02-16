<?php

use App\Models\Campaign;
use App\Models\Encounter;
use App\Models\GameSession;
use App\Models\Npc;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->campaign = Campaign::factory()->for($this->user)->create();
    $this->session = GameSession::factory()->for($this->campaign)->create();
    $this->encounter = Encounter::factory()->for($this->session)->create();

    $this->actingAs($this->user);
});

test('encounter card shows add npc button', function () {
    Livewire::test('sessions.encounter-card', ['encounter' => $this->encounter])
        ->assertSee(__('Add NPC'));
});

test('can add npc to encounter', function () {
    $npc = Npc::factory()->for($this->campaign)->create(['name' => 'Gandalf']);

    Livewire::test('sessions.encounter-card', ['encounter' => $this->encounter])
        ->call('openNpcForm')
        ->set('selectedNpcId', $npc->id)
        ->set('npcHpMax', 50)
        ->set('npcArmorClass', 15)
        ->call('addNpcToEncounter')
        ->assertDispatched('$refresh');

    $encounterNpc = $this->encounter->npcs()->first();
    expect($encounterNpc)->not->toBeNull()
        ->and($encounterNpc->name)->toBe('Gandalf')
        ->and($encounterNpc->npc_id)->toBe($npc->id)
        ->and($encounterNpc->hp_max)->toBe(50)
        ->and($encounterNpc->armor_class)->toBe(15);
});

test('add npc to encounter validates required fields', function () {
    Livewire::test('sessions.encounter-card', ['encounter' => $this->encounter])
        ->call('openNpcForm')
        ->set('selectedNpcId', null)
        ->call('addNpcToEncounter')
        ->assertHasErrors(['selectedNpcId']);
});

test('can delete npc from encounter', function () {
    $npc = Npc::factory()->for($this->campaign)->create();

    $encounterNpc = $this->encounter->npcs()->create([
        'npc_id' => $npc->id,
        'name' => $npc->name,
        'hp_max' => 20,
        'armor_class' => 12,
    ]);

    Livewire::test('sessions.encounter-card', ['encounter' => $this->encounter])
        ->call('deleteNpc', $encounterNpc->id)
        ->assertDispatched('$refresh');

    expect($this->encounter->npcs()->count())->toBe(0);
});
