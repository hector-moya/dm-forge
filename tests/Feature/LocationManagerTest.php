<?php

use App\Ai\Agents\ImagePromptCrafter;
use App\Ai\Agents\LocationGenerator;
use App\Models\Campaign;
use App\Models\Location;
use App\Models\Npc;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Image;
use Livewire\Livewire;

test('location manager page loads for campaign owner', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('campaigns.locations', $campaign))
        ->assertOk();
});

test('location manager denies access to non-owner', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $campaign = Campaign::factory()->for($other)->create();

    $this->actingAs($user)
        ->get(route('campaigns.locations', $campaign))
        ->assertForbidden();
});

test('location manager lists locations', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    Location::factory()->for($campaign)->create(['name' => 'Whispering Woods']);

    Livewire::actingAs($user)
        ->test('pages::campaigns.location-manager', ['campaign' => $campaign])
        ->assertSee('Whispering Woods');
});

test('location manager can create a location', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.location-manager', ['campaign' => $campaign])
        ->call('openForm')
        ->assertSet('showForm', true)
        ->set('locationName', 'Dragon Peak')
        ->set('locationRegion', 'Northern Mountains')
        ->set('locationDescription', 'A towering peak where dragons nest')
        ->call('save');

    $location = $campaign->locations()->where('name', 'Dragon Peak')->first();
    expect($location)->not->toBeNull();
    expect($location->region)->toBe('Northern Mountains');
});

test('location manager can edit a location', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $location = Location::factory()->for($campaign)->create(['name' => 'Old Forest']);

    Livewire::actingAs($user)
        ->test('pages::campaigns.location-manager', ['campaign' => $campaign])
        ->call('openForm', $location->id)
        ->assertSet('locationName', 'Old Forest')
        ->set('locationName', 'New Forest')
        ->call('save');

    expect($location->fresh()->name)->toBe('New Forest');
});

test('location manager can delete a location', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $location = Location::factory()->for($campaign)->create();

    Livewire::actingAs($user)
        ->test('pages::campaigns.location-manager', ['campaign' => $campaign])
        ->call('delete', $location->id);

    expect($campaign->locations()->count())->toBe(0);
});

test('location manager search filters locations', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    Location::factory()->for($campaign)->create(['name' => 'Whispering Woods']);
    Location::factory()->for($campaign)->create(['name' => 'Dragon Peak']);

    Livewire::actingAs($user)
        ->test('pages::campaigns.location-manager', ['campaign' => $campaign])
        ->set('search', 'Dragon')
        ->assertSee('Dragon Peak')
        ->assertDontSee('Whispering Woods');
});

test('location manager region filter works', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    Location::factory()->for($campaign)->create(['name' => 'Forest Town', 'region' => 'Greenlands']);
    Location::factory()->for($campaign)->create(['name' => 'Ice Castle', 'region' => 'Northern Wastes']);

    Livewire::actingAs($user)
        ->test('pages::campaigns.location-manager', ['campaign' => $campaign])
        ->set('regionFilter', 'Northern Wastes')
        ->assertSee('Ice Castle')
        ->assertDontSee('Forest Town');
});

test('location manager can set parent location', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $parent = Location::factory()->for($campaign)->create(['name' => 'Kingdom']);

    Livewire::actingAs($user)
        ->test('pages::campaigns.location-manager', ['campaign' => $campaign])
        ->call('openForm')
        ->set('locationName', 'Castle')
        ->set('locationParentId', $parent->id)
        ->call('save');

    $child = $campaign->locations()->where('name', 'Castle')->first();
    expect($child->parent_location_id)->toBe($parent->id);
});

test('location manager shows detail with sub-locations and npcs', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $location = Location::factory()->for($campaign)->create(['name' => 'Capital City']);
    Location::factory()->for($campaign)->create(['name' => 'Market District', 'parent_location_id' => $location->id]);
    Npc::factory()->for($campaign)->create(['name' => 'Guard Captain', 'location_id' => $location->id]);

    Livewire::actingAs($user)
        ->test('pages::campaigns.location-manager', ['campaign' => $campaign])
        ->call('viewLocation', $location->id)
        ->assertSet('viewingLocationId', $location->id);
});

test('location manager generates location with ai', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    LocationGenerator::fake([
        [
            'name' => 'The Whispering Woods',
            'description' => 'A dense forest where trees murmur',
            'region' => 'Northern Marches',
            'history' => 'Once home to ancient elves',
            'notable_features' => ['Stone circle', 'Mushroom groves'],
            'suggested_parent_location' => null,
            'suggested_faction' => null,
        ],
    ]);

    Livewire::actingAs($user)
        ->test('pages::campaigns.location-manager', ['campaign' => $campaign])
        ->call('openGenerateModal')
        ->assertSet('showGenerateModal', true)
        ->set('generateContext', 'A haunted forest')
        ->call('generate')
        ->assertSet('showGenerateModal', false)
        ->assertSet('showForm', true)
        ->assertSet('locationName', 'The Whispering Woods')
        ->assertSet('locationRegion', 'Northern Marches');

    LocationGenerator::assertPrompted(fn ($prompt) => $prompt->contains('haunted forest'));
});

test('location manager can generate image for location', function () {
    Storage::fake('public');
    ImagePromptCrafter::fake();
    Image::fake();

    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $location = Location::factory()->for($campaign)->create(['name' => 'Dark Forest']);

    Livewire::actingAs($user)
        ->test('pages::campaigns.location-manager', ['campaign' => $campaign])
        ->call('generateImage', $location->id);

    expect($location->fresh()->image_path)->not->toBeNull();
    ImagePromptCrafter::assertPrompted(fn ($prompt) => $prompt->contains('location'));
    Image::assertGenerated(fn () => true);
});

test('location manager handles ai failure gracefully', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    LocationGenerator::fake(function () {
        throw new \RuntimeException('API error');
    });

    Livewire::actingAs($user)
        ->test('pages::campaigns.location-manager', ['campaign' => $campaign])
        ->call('openGenerateModal')
        ->call('generate')
        ->assertSet('generating', false)
        ->assertSet('showForm', false);
});
