<?php

use App\Ai\Agents\ImagePromptCrafter;
use App\Livewire\Library\MonsterLibrary;
use App\Models\CustomMonster;
use App\Models\SrdMonster;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Image;
use Livewire\Livewire;

test('monster library page requires authentication', function () {
    $this->get(route('library.monsters'))->assertRedirect(route('login'));
});

test('monster library page loads for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('library.monsters'))
        ->assertOk()
        ->assertSeeLivewire(MonsterLibrary::class);
});

test('monster library displays srd monsters', function () {
    $user = User::factory()->create();
    SrdMonster::query()->create([
        'index' => 'goblin',
        'name' => 'Goblin',
        'type' => 'Humanoid',
        'armor_class' => 15,
        'hit_points' => 7,
        'challenge_rating' => 0.25,
        'xp' => 50,
    ]);

    Livewire::actingAs($user)
        ->test(MonsterLibrary::class)
        ->assertSee('Goblin')
        ->assertSee('Humanoid');
});

test('monster library displays custom monsters', function () {
    $user = User::factory()->create();
    CustomMonster::factory()->for($user)->create([
        'name' => 'Shadow Drake',
        'type' => 'Dragon',
    ]);

    Livewire::actingAs($user)
        ->test(MonsterLibrary::class)
        ->assertSee('Shadow Drake');
});

test('monster library filters by search term', function () {
    $user = User::factory()->create();
    SrdMonster::query()->create(['index' => 'goblin', 'name' => 'Goblin', 'armor_class' => 15, 'hit_points' => 7]);
    SrdMonster::query()->create(['index' => 'dragon-red', 'name' => 'Red Dragon', 'armor_class' => 19, 'hit_points' => 256]);

    Livewire::actingAs($user)
        ->test(MonsterLibrary::class)
        ->set('search', 'Goblin')
        ->assertSee('Goblin')
        ->assertDontSee('Red Dragon');
});

test('monster library filters by type', function () {
    $user = User::factory()->create();
    SrdMonster::query()->create(['index' => 'goblin', 'name' => 'Goblin', 'type' => 'Humanoid', 'armor_class' => 15, 'hit_points' => 7]);
    SrdMonster::query()->create(['index' => 'imp', 'name' => 'Imp', 'type' => 'Fiend', 'armor_class' => 13, 'hit_points' => 10]);

    Livewire::actingAs($user)
        ->test(MonsterLibrary::class)
        ->set('typeFilter', 'Fiend')
        ->assertSee('Imp')
        ->assertDontSee('Goblin');
});

test('monster library filters by source', function () {
    $user = User::factory()->create();
    SrdMonster::query()->create(['index' => 'goblin', 'name' => 'Goblin', 'armor_class' => 15, 'hit_points' => 7]);
    CustomMonster::factory()->for($user)->create(['name' => 'Shadow Drake']);

    Livewire::actingAs($user)
        ->test(MonsterLibrary::class)
        ->set('sourceFilter', 'custom')
        ->assertSee('Shadow Drake')
        ->assertDontSee('Goblin');
});

test('user can create custom monster', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MonsterLibrary::class)
        ->call('openCustomForm')
        ->assertSet('showCustomForm', true)
        ->set('customName', 'Flame Golem')
        ->set('customType', 'Construct')
        ->set('customArmorClass', 18)
        ->set('customHitPoints', 120)
        ->set('customChallengeRating', 8)
        ->set('customXp', 3900)
        ->call('saveCustomMonster')
        ->assertSet('showCustomForm', false);

    expect(CustomMonster::query()->where('user_id', $user->id)->where('name', 'Flame Golem')->exists())->toBeTrue();
});

test('user can edit custom monster', function () {
    $user = User::factory()->create();
    $monster = CustomMonster::factory()->for($user)->create(['name' => 'Old Name']);

    Livewire::actingAs($user)
        ->test(MonsterLibrary::class)
        ->call('editCustomMonster', $monster->id)
        ->assertSet('showCustomForm', true)
        ->assertSet('customName', 'Old Name')
        ->set('customName', 'New Name')
        ->call('saveCustomMonster')
        ->assertSet('showCustomForm', false);

    expect($monster->fresh()->name)->toBe('New Name');
});

test('user can delete custom monster', function () {
    $user = User::factory()->create();
    $monster = CustomMonster::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(MonsterLibrary::class)
        ->call('deleteCustomMonster', $monster->id);

    expect(CustomMonster::query()->find($monster->id))->toBeNull();
});

test('user cannot edit another users custom monster', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $monster = CustomMonster::factory()->for($otherUser)->create();

    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    Livewire::actingAs($user)
        ->test(MonsterLibrary::class)
        ->call('editCustomMonster', $monster->id);
});

test('monster library can generate image for custom monster', function () {
    Storage::fake('public');
    ImagePromptCrafter::fake();
    Image::fake();

    $user = User::factory()->create();
    $monster = CustomMonster::factory()->for($user)->create(['name' => 'Shadow Drake']);

    Livewire::actingAs($user)
        ->test(MonsterLibrary::class)
        ->call('generateImage', $monster->id);

    expect($monster->fresh()->image_path)->not->toBeNull();
    ImagePromptCrafter::assertPrompted(fn ($prompt) => $prompt->contains('monster'));
    Image::assertGenerated(fn () => true);
});

test('custom monster validation requires name', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MonsterLibrary::class)
        ->call('openCustomForm')
        ->set('customName', '')
        ->call('saveCustomMonster')
        ->assertHasErrors(['customName']);
});
