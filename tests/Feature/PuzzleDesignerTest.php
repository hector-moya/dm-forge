<?php

use App\Ai\Agents\PuzzleDesigner;
use App\Models\Campaign;
use App\Models\GameSession;
use App\Models\Puzzle;
use App\Models\Scene;
use App\Models\User;
use Livewire\Livewire;

test('puzzle designer agent implements structured output', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();

    $agent = new PuzzleDesigner($campaign);

    expect($agent)->toBeInstanceOf(\Laravel\Ai\Contracts\HasStructuredOutput::class);
    expect($agent)->toBeInstanceOf(\Laravel\Ai\Contracts\HasTools::class);
    expect($agent->instructions())->toContain('D&D puzzle designer');
});

test('puzzle can be saved to a scene via scene card', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $scene = Scene::factory()->for($session, 'gameSession')->create();

    Livewire::actingAs($user)
        ->test('sessions.scene-card', ['scene' => $scene, 'sessionId' => $session->id])
        ->call('openPuzzleForm')
        ->assertSet('showPuzzleForm', true)
        ->set('puzzleName', 'The Sphinx\'s Riddle')
        ->set('puzzleDescription', 'What has a heart that doesn\'t beat?')
        ->set('puzzleSolution', 'An artichoke')
        ->set('puzzleHint1', 'Think about food')
        ->set('puzzleHint2', 'It grows in a garden')
        ->set('puzzleHint3', 'It\'s a vegetable')
        ->set('puzzleDifficulty', 'easy')
        ->set('puzzleType', 'riddle')
        ->call('savePuzzle');

    $puzzle = $scene->puzzles()->first();
    expect($puzzle)->not->toBeNull();
    expect($puzzle->name)->toBe('The Sphinx\'s Riddle');
    expect($puzzle->difficulty)->toBe('easy');
    expect($puzzle->puzzle_type)->toBe('riddle');
    expect($puzzle->hint_tier_1)->toBe('Think about food');
    expect($puzzle->campaign_id)->toBe($campaign->id);
});

test('puzzle can be edited via scene card', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $scene = Scene::factory()->for($session, 'gameSession')->create();
    $puzzle = Puzzle::factory()->for($campaign)->create([
        'scene_id' => $scene->id,
        'name' => 'Original Puzzle',
    ]);

    Livewire::actingAs($user)
        ->test('sessions.scene-card', ['scene' => $scene, 'sessionId' => $session->id])
        ->call('openPuzzleForm', $puzzle->id)
        ->assertSet('puzzleName', 'Original Puzzle')
        ->set('puzzleName', 'Updated Puzzle')
        ->call('savePuzzle');

    expect($puzzle->fresh()->name)->toBe('Updated Puzzle');
});

test('puzzle can be deleted from scene card', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $scene = Scene::factory()->for($session, 'gameSession')->create();
    $puzzle = Puzzle::factory()->for($campaign)->create(['scene_id' => $scene->id]);

    Livewire::actingAs($user)
        ->test('sessions.scene-card', ['scene' => $scene, 'sessionId' => $session->id])
        ->call('deletePuzzle', $puzzle->id);

    expect(Puzzle::find($puzzle->id))->toBeNull();
});

test('puzzle solved status can be toggled', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $scene = Scene::factory()->for($session, 'gameSession')->create();
    $puzzle = Puzzle::factory()->for($campaign)->create([
        'scene_id' => $scene->id,
        'is_solved' => false,
    ]);

    Livewire::actingAs($user)
        ->test('sessions.scene-card', ['scene' => $scene, 'sessionId' => $session->id])
        ->call('togglePuzzleSolved', $puzzle->id);

    expect($puzzle->fresh()->is_solved)->toBeTrue();
});

test('ai generates puzzle and populates form', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $scene = Scene::factory()->for($session, 'gameSession')->create();

    PuzzleDesigner::fake([
        [
            'name' => 'The Guardian\'s Lock',
            'description' => 'Three dials with ancient runes must be aligned',
            'solution' => 'Align the runes to spell OPEN in Dwarvish',
            'hint_tier_1' => 'The dwarves wrote from right to left',
            'hint_tier_2' => 'Each dial has one rune that means a letter',
            'hint_tier_3' => 'The word is simple — what do you want the door to do?',
        ],
    ]);

    Livewire::actingAs($user)
        ->test('sessions.scene-card', ['scene' => $scene, 'sessionId' => $session->id])
        ->call('openGeneratePuzzleModal')
        ->assertSet('showGeneratePuzzleModal', true)
        ->set('generatePuzzleDifficulty', 'hard')
        ->set('generatePuzzleType', 'cipher')
        ->set('generatePuzzleContext', 'A locked door in a dwarven ruin')
        ->call('generatePuzzle')
        ->assertSet('showGeneratePuzzleModal', false)
        ->assertSet('showPuzzleForm', true)
        ->assertSet('puzzleName', 'The Guardian\'s Lock')
        ->assertSet('puzzleDescription', 'Three dials with ancient runes must be aligned')
        ->assertSet('puzzleSolution', 'Align the runes to spell OPEN in Dwarvish')
        ->assertSet('puzzleHint1', 'The dwarves wrote from right to left')
        ->assertSet('puzzleDifficulty', 'hard')
        ->assertSet('puzzleType', 'cipher');

    PuzzleDesigner::assertPrompted(fn ($prompt) => $prompt->contains('dwarven ruin'));
});

test('ai puzzle generation handles failure gracefully', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create();
    $scene = Scene::factory()->for($session, 'gameSession')->create();

    PuzzleDesigner::fake(function () {
        throw new \RuntimeException('API rate limit exceeded');
    });

    Livewire::actingAs($user)
        ->test('sessions.scene-card', ['scene' => $scene, 'sessionId' => $session->id])
        ->call('openGeneratePuzzleModal')
        ->call('generatePuzzle')
        ->assertSet('generatingPuzzle', false)
        ->assertSet('showPuzzleForm', false);
});

test('session runner can reveal puzzle hints progressively', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create(['status' => 'running']);
    $scene = Scene::factory()->for($session, 'gameSession')->create(['is_revealed' => true]);
    $puzzle = Puzzle::factory()->for($campaign)->withHints()->create([
        'scene_id' => $scene->id,
    ]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Sessions\SessionRunner::class, ['session' => $session])
        ->assertSee($puzzle->name);

    $component->call('revealHint', $puzzle->id, 1)
        ->assertSet("revealedHints.{$puzzle->id}", 1);

    $component->call('revealHint', $puzzle->id, 2)
        ->assertSet("revealedHints.{$puzzle->id}", 2);

    $component->call('revealHint', $puzzle->id, 3)
        ->assertSet("revealedHints.{$puzzle->id}", 3);
});

test('session runner can toggle puzzle solved status', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create();
    $session = GameSession::factory()->for($campaign)->create(['status' => 'running']);
    $scene = Scene::factory()->for($session, 'gameSession')->create(['is_revealed' => true]);
    $puzzle = Puzzle::factory()->for($campaign)->create([
        'scene_id' => $scene->id,
        'is_solved' => false,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Sessions\SessionRunner::class, ['session' => $session])
        ->call('togglePuzzleSolved', $puzzle->id);

    expect($puzzle->fresh()->is_solved)->toBeTrue();
});

test('puzzle factory creates valid puzzles', function () {
    $puzzle = Puzzle::factory()->create();

    expect($puzzle->name)->toBeString();
    expect($puzzle->difficulty)->toBeIn(['easy', 'medium', 'hard']);
    expect($puzzle->puzzle_type)->toBeIn(['riddle', 'logic', 'physical', 'cipher', 'pattern']);
    expect($puzzle->is_solved)->toBeFalse();
});

test('puzzle factory solved state works', function () {
    $puzzle = Puzzle::factory()->solved()->create();

    expect($puzzle->is_solved)->toBeTrue();
});

test('puzzle factory withHints state includes all hints', function () {
    $puzzle = Puzzle::factory()->withHints()->create();

    expect($puzzle->hint_tier_1)->not->toBeNull();
    expect($puzzle->hint_tier_2)->not->toBeNull();
    expect($puzzle->hint_tier_3)->not->toBeNull();
});
