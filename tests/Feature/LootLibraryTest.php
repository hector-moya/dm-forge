<?php

use App\Ai\Agents\ImagePromptCrafter;
use App\Livewire\Library\LootLibrary;
use App\Models\CustomLoot;
use App\Models\SrdEquipment;
use App\Models\SrdMagicItem;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Image;
use Livewire\Livewire;

test('loot library page requires authentication', function () {
    $this->get(route('library.loot'))->assertRedirect(route('login'));
});

test('loot library page loads for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('library.loot'))
        ->assertOk()
        ->assertSeeLivewire(LootLibrary::class);
});

test('loot library displays srd equipment', function () {
    $user = User::factory()->create();
    SrdEquipment::query()->create([
        'index' => 'longsword',
        'name' => 'Longsword',
        'equipment_category' => 'Weapon',
        'cost_gp' => 15.0,
        'weight' => 3.0,
    ]);

    Livewire::actingAs($user)
        ->test(LootLibrary::class)
        ->assertSee('Longsword')
        ->assertSee('Weapon');
});

test('loot library displays srd magic items', function () {
    $user = User::factory()->create();
    SrdMagicItem::query()->create([
        'index' => 'bag-of-holding',
        'name' => 'Bag of Holding',
        'equipment_category' => 'Wondrous Items',
        'rarity' => 'Uncommon',
        'description' => 'This bag has an interior space considerably larger than its outside dimensions.',
    ]);

    Livewire::actingAs($user)
        ->test(LootLibrary::class)
        ->assertSee('Bag of Holding')
        ->assertSee('Uncommon');
});

test('loot library displays custom loot', function () {
    $user = User::factory()->create();
    CustomLoot::factory()->for($user)->create([
        'name' => 'Dragon Scale Shield',
        'category' => 'equipment',
    ]);

    Livewire::actingAs($user)
        ->test(LootLibrary::class)
        ->assertSee('Dragon Scale Shield');
});

test('loot library filters by search term', function () {
    $user = User::factory()->create();
    SrdEquipment::query()->create(['index' => 'longsword', 'name' => 'Longsword', 'equipment_category' => 'Weapon']);
    SrdEquipment::query()->create(['index' => 'shield', 'name' => 'Shield', 'equipment_category' => 'Armor']);

    Livewire::actingAs($user)
        ->test(LootLibrary::class)
        ->set('search', 'Longsword')
        ->assertSee('Longsword')
        ->assertDontSee('Shield');
});

test('loot library filters by source', function () {
    $user = User::factory()->create();
    SrdEquipment::query()->create(['index' => 'longsword', 'name' => 'Longsword', 'equipment_category' => 'Weapon']);
    CustomLoot::factory()->for($user)->create(['name' => 'Dragon Fang']);

    Livewire::actingAs($user)
        ->test(LootLibrary::class)
        ->set('sourceFilter', 'custom')
        ->assertSee('Dragon Fang')
        ->assertDontSee('Longsword');
});

test('user can create custom loot', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(LootLibrary::class)
        ->call('openCustomForm')
        ->assertSet('showCustomForm', true)
        ->set('customName', 'Enchanted Ring')
        ->set('customCategory', 'magic_item')
        ->set('customRarity', 'Rare')
        ->set('customValueGp', 500)
        ->set('customDescription', 'A ring that glows faintly blue.')
        ->call('saveCustomLoot')
        ->assertSet('showCustomForm', false);

    expect(CustomLoot::query()->where('user_id', $user->id)->where('name', 'Enchanted Ring')->exists())->toBeTrue();
});

test('user can edit custom loot', function () {
    $user = User::factory()->create();
    $loot = CustomLoot::factory()->for($user)->create(['name' => 'Old Ring']);

    Livewire::actingAs($user)
        ->test(LootLibrary::class)
        ->call('editCustomLoot', $loot->id)
        ->assertSet('showCustomForm', true)
        ->assertSet('customName', 'Old Ring')
        ->set('customName', 'New Ring')
        ->call('saveCustomLoot')
        ->assertSet('showCustomForm', false);

    expect($loot->fresh()->name)->toBe('New Ring');
});

test('user can delete custom loot', function () {
    $user = User::factory()->create();
    $loot = CustomLoot::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(LootLibrary::class)
        ->call('deleteCustomLoot', $loot->id);

    expect(CustomLoot::query()->find($loot->id))->toBeNull();
});

test('user cannot edit another users custom loot', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $loot = CustomLoot::factory()->for($otherUser)->create();

    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    Livewire::actingAs($user)
        ->test(LootLibrary::class)
        ->call('editCustomLoot', $loot->id);
});

test('loot library can generate image for custom loot', function () {
    Storage::fake('public');
    ImagePromptCrafter::fake();
    Image::fake();

    $user = User::factory()->create();
    $loot = CustomLoot::factory()->for($user)->create(['name' => 'Frostbrand']);

    Livewire::actingAs($user)
        ->test(LootLibrary::class)
        ->call('generateImage', $loot->id);

    expect($loot->fresh()->image_path)->not->toBeNull();
    ImagePromptCrafter::assertPrompted(fn ($prompt) => $prompt->contains('loot'));
    Image::assertGenerated(fn () => true);
});

test('custom loot validation requires name', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(LootLibrary::class)
        ->call('openCustomForm')
        ->set('customName', '')
        ->call('saveCustomLoot')
        ->assertHasErrors(['customName']);
});
