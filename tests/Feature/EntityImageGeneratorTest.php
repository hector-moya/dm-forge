<?php

use App\Ai\Agents\ImagePromptCrafter;
use App\Models\Campaign;
use App\Models\CustomLoot;
use App\Models\CustomMonster;
use App\Models\Faction;
use App\Models\Location;
use App\Models\Npc;
use App\Models\Scene;
use App\Models\User;
use App\Services\EntityImageGenerator;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Image;

beforeEach(function () {
    Storage::fake('public');

    ImagePromptCrafter::fake([
        [
            'prompt' => 'A dramatic fantasy scene in dark painterly style.',
            'orientation' => 'square',
        ],
    ]);

    Image::fake();
});

test('generates image for npc and updates model', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $npc = Npc::factory()->for($campaign)->create(['name' => 'Gornik']);

    $path = app(EntityImageGenerator::class)->generate($npc, 'npc');

    expect($path)->not->toBeNull();
    expect($path)->toStartWith('images/npcs/');
    expect($npc->fresh()->image_path)->toBe($path);

    ImagePromptCrafter::assertPrompted(fn ($prompt) => $prompt->contains('npc'));
    Image::assertGenerated(fn ($prompt) => true);
});

test('generates image for custom monster', function () {
    $user = User::factory()->create();
    $monster = CustomMonster::factory()->for($user)->create(['name' => 'Shadow Drake']);

    $path = app(EntityImageGenerator::class)->generate($monster, 'monster');

    expect($path)->not->toBeNull();
    expect($path)->toStartWith('images/monsters/');
    expect($monster->fresh()->image_path)->toBe($path);
});

test('generates image for location', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $location = Location::factory()->for($campaign)->create(['name' => 'Dark Forest']);

    $path = app(EntityImageGenerator::class)->generate($location, 'location');

    expect($path)->not->toBeNull();
    expect($path)->toStartWith('images/locations/');
    expect($location->fresh()->image_path)->toBe($path);
});

test('generates image for faction', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $faction = Faction::factory()->for($campaign)->create(['name' => 'Iron Brotherhood']);

    $path = app(EntityImageGenerator::class)->generate($faction, 'faction');

    expect($path)->not->toBeNull();
    expect($path)->toStartWith('images/factions/');
    expect($faction->fresh()->image_path)->toBe($path);
});

test('generates image for custom loot', function () {
    $user = User::factory()->create();
    $loot = CustomLoot::factory()->for($user)->create(['name' => 'Frostbrand']);

    $path = app(EntityImageGenerator::class)->generate($loot, 'loot');

    expect($path)->not->toBeNull();
    expect($path)->toStartWith('images/loots/');
    expect($loot->fresh()->image_path)->toBe($path);
});

test('generates image for scene', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = \App\Models\GameSession::factory()->create(['campaign_id' => $campaign->id]);
    $scene = Scene::factory()->create(['game_session_id' => $session->id, 'title' => 'Dragon Lair']);

    $path = app(EntityImageGenerator::class)->generate($scene, 'scene');

    expect($path)->not->toBeNull();
    expect($path)->toStartWith('images/scenes/');
    expect($scene->fresh()->image_path)->toBe($path);
});

test('includes extra context in prompt', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $npc = Npc::factory()->for($campaign)->create(['name' => 'Test NPC']);

    app(EntityImageGenerator::class)->generate($npc, 'npc', 'Set in a medieval tavern');

    ImagePromptCrafter::assertPrompted(fn ($prompt) => $prompt->contains('medieval tavern'));
});

test('returns null on failure', function () {
    ImagePromptCrafter::fake(function () {
        throw new \RuntimeException('API error');
    });

    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $npc = Npc::factory()->for($campaign)->create();

    $path = app(EntityImageGenerator::class)->generate($npc, 'npc');

    expect($path)->toBeNull();
    expect($npc->fresh()->image_path)->toBeNull();
});

test('deletes old image on regeneration', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $npc = Npc::factory()->for($campaign)->create(['image_path' => 'images/npcs/old_image.webp']);

    Storage::disk('public')->put('images/npcs/old_image.webp', 'fake-image-data');
    expect(Storage::disk('public')->exists('images/npcs/old_image.webp'))->toBeTrue();

    $path = app(EntityImageGenerator::class)->generate($npc, 'npc');

    expect($path)->not->toBeNull();
    expect($path)->not->toBe('images/npcs/old_image.webp');
    expect(Storage::disk('public')->exists('images/npcs/old_image.webp'))->toBeFalse();
});
