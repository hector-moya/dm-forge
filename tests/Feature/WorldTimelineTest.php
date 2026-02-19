<?php

use App\Models\Campaign;
use App\Models\User;
use App\Models\WorldEvent;
use Livewire\Livewire;

test('guests cannot access world timeline', function () {
    $campaign = Campaign::factory()->create();

    $this->get(route('campaigns.timeline', $campaign))->assertRedirect(route('login'));
});

test('users can view their campaign timeline', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('campaigns.timeline', $campaign))
        ->assertOk();
});

test('users cannot view other users timelines', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $campaign = Campaign::factory()->for($other)->create();

    $this->actingAs($user)
        ->get(route('campaigns.timeline', $campaign))
        ->assertForbidden();
});

test('users can create a world event', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.world-timeline', ['campaign' => $campaign])
        ->call('openEventForm')
        ->assertSet('showEventForm', true)
        ->set('eventTitle', 'The Dragon Awakens')
        ->set('eventDescription', 'An ancient dragon stirs beneath the mountain')
        ->set('eventType', 'custom')
        ->set('eventOccurredAt', '2026-01-15T14:00')
        ->call('saveEvent');

    expect($campaign->worldEvents()->where('title', 'The Dragon Awakens')->exists())->toBeTrue();
});

test('users can edit a world event', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $event = WorldEvent::factory()->for($campaign)->create(['title' => 'Original Title']);

    Livewire::actingAs($user)
        ->test('pages::campaigns.world-timeline', ['campaign' => $campaign])
        ->call('openEventForm', $event->id)
        ->assertSet('eventTitle', 'Original Title')
        ->set('eventTitle', 'Updated Title')
        ->call('saveEvent');

    expect($event->fresh()->title)->toBe('Updated Title');
});

test('users can delete a world event', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $event = WorldEvent::factory()->for($campaign)->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.world-timeline', ['campaign' => $campaign])
        ->call('deleteEvent', $event->id);

    expect(WorldEvent::find($event->id))->toBeNull();
});

test('timeline can be filtered by event type', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    WorldEvent::factory()->for($campaign)->create(['event_type' => 'faction_movement', 'title' => 'Faction Event']);
    WorldEvent::factory()->for($campaign)->create(['event_type' => 'custom', 'title' => 'Custom Event']);

    $component = Livewire::actingAs($user)
        ->test('pages::campaigns.world-timeline', ['campaign' => $campaign])
        ->assertSee('Faction Event')
        ->assertSee('Custom Event');

    $component->set('filterType', 'faction_movement')
        ->assertSee('Faction Event')
        ->assertDontSee('Custom Event');
});

test('world event can be associated with faction and location', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $faction = $campaign->factions()->create(['name' => 'Dark Brotherhood']);
    $location = $campaign->locations()->create(['name' => 'Shadow Keep']);

    Livewire::actingAs($user)
        ->test('pages::campaigns.world-timeline', ['campaign' => $campaign])
        ->call('openEventForm')
        ->set('eventTitle', 'Brotherhood Moves')
        ->set('eventDescription', 'They march on Shadow Keep')
        ->set('eventType', 'faction_movement')
        ->set('eventFactionId', $faction->id)
        ->set('eventLocationId', $location->id)
        ->set('eventOccurredAt', '2026-01-20T10:00')
        ->call('saveEvent');

    $event = $campaign->worldEvents()->first();
    expect($event->faction_id)->toBe($faction->id);
    expect($event->location_id)->toBe($location->id);
});

test('world event factory creates valid events', function () {
    $event = WorldEvent::factory()->create();

    expect($event->title)->toBeString();
    expect($event->event_type)->toBeIn(['faction_movement', 'consequence_resolved', 'npc_change', 'territory_change', 'custom']);
});
