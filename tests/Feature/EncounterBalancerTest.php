<?php

use App\DataTransferObjects\EncounterDifficulty;
use App\Enums\DifficultyRating;
use App\Models\Campaign;
use App\Models\Character;
use App\Models\Encounter;
use App\Models\GameSession;
use App\Services\EncounterBalancer;

test('trivial encounter with no monsters', function () {
    $campaign = Campaign::factory()->create();
    $characters = Character::factory()->for($campaign)->count(4)->create(['level' => 5]);
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $balancer = new EncounterBalancer;
    $result = $balancer->calculate($encounter, $characters);

    expect($result)
        ->toBeInstanceOf(EncounterDifficulty::class)
        ->rating->toBe(DifficultyRating::Trivial)
        ->adjustedXp->toBe(0)
        ->rawXp->toBe(0);
});

test('easy encounter for level 1 party', function () {
    $campaign = Campaign::factory()->create();
    $characters = Character::factory()->for($campaign)->count(4)->create(['level' => 1]);
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    // 2 goblins at 50 XP each = 100 raw XP, x1.5 multiplier = 150 adjusted
    // Party of 4 level 1: easy=100, medium=200, hard=300, deadly=400
    // 150 >= 100 = Easy
    $encounter->monsters()->create([
        'name' => 'Goblin 1',
        'hp_max' => 7, 'hp_current' => 7, 'armor_class' => 15,
        'xp' => 50, 'challenge_rating' => 0.25,
    ]);
    $encounter->monsters()->create([
        'name' => 'Goblin 2',
        'hp_max' => 7, 'hp_current' => 7, 'armor_class' => 15,
        'xp' => 50, 'challenge_rating' => 0.25,
    ]);

    $balancer = new EncounterBalancer;
    $result = $balancer->calculate($encounter, $characters);

    expect($result->rawXp)->toBe(100)
        ->and($result->multiplier)->toBe(1.5)
        ->and($result->adjustedXp)->toBe(150)
        ->and($result->rating)->toBe(DifficultyRating::Easy);
});

test('hard encounter with multiple monsters', function () {
    $campaign = Campaign::factory()->create();
    $characters = Character::factory()->for($campaign)->count(4)->create(['level' => 3]);
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    // 1 ogre (450 XP) + 4 goblins (50 each = 200) = 650 raw
    // 5 monsters total, x2 multiplier = 1300 adjusted
    // Party of 4 level 3: easy=300, medium=600, hard=900, deadly=1600
    // 1300 >= 900 = Hard
    $encounter->monsters()->create([
        'name' => 'Ogre', 'hp_max' => 59, 'hp_current' => 59, 'armor_class' => 11,
        'xp' => 450, 'challenge_rating' => 2,
    ]);
    foreach (range(1, 4) as $i) {
        $encounter->monsters()->create([
            'name' => "Goblin {$i}", 'hp_max' => 7, 'hp_current' => 7, 'armor_class' => 15,
            'xp' => 50, 'challenge_rating' => 0.25,
        ]);
    }

    $balancer = new EncounterBalancer;
    $result = $balancer->calculate($encounter, $characters);

    expect($result->rawXp)->toBe(650)
        ->and($result->multiplier)->toBe(2.0)
        ->and($result->adjustedXp)->toBe(1300)
        ->and($result->rating)->toBe(DifficultyRating::Hard);
});

test('multiplier scales with monster count', function () {
    $balancer = new EncounterBalancer;

    expect($balancer->getMultiplier(0))->toBe(1.0)
        ->and($balancer->getMultiplier(1))->toBe(1.0)
        ->and($balancer->getMultiplier(2))->toBe(1.5)
        ->and($balancer->getMultiplier(3))->toBe(2.0)
        ->and($balancer->getMultiplier(6))->toBe(2.0)
        ->and($balancer->getMultiplier(7))->toBe(2.5)
        ->and($balancer->getMultiplier(10))->toBe(2.5)
        ->and($balancer->getMultiplier(11))->toBe(3.0)
        ->and($balancer->getMultiplier(14))->toBe(3.0)
        ->and($balancer->getMultiplier(15))->toBe(4.0)
        ->and($balancer->getMultiplier(20))->toBe(4.0);
});

test('small party increases multiplier', function () {
    $balancer = new EncounterBalancer;

    expect($balancer->getMultiplier(1, 2))->toBe(1.5)
        ->and($balancer->getMultiplier(2, 2))->toBe(2.0);
});

test('large party decreases multiplier', function () {
    $balancer = new EncounterBalancer;

    expect($balancer->getMultiplier(2, 6))->toBe(1.0)
        ->and($balancer->getMultiplier(3, 6))->toBe(1.5);
});

test('party thresholds sum correctly', function () {
    $campaign = Campaign::factory()->create();
    $characters = Character::factory()->for($campaign)->count(4)->create(['level' => 1]);
    $session = GameSession::factory()->for($campaign)->create();
    $encounter = Encounter::factory()->for($session)->create();

    $balancer = new EncounterBalancer;
    $result = $balancer->calculate($encounter, $characters);

    expect($result->partyThresholds)
        ->toBe(['easy' => 100, 'medium' => 200, 'hard' => 300, 'deadly' => 400]);
});

test('difficulty rating enum has correct colors', function () {
    expect(DifficultyRating::Trivial->color())->toBe('zinc')
        ->and(DifficultyRating::Easy->color())->toBe('green')
        ->and(DifficultyRating::Medium->color())->toBe('amber')
        ->and(DifficultyRating::Hard->color())->toBe('orange')
        ->and(DifficultyRating::Deadly->color())->toBe('red');
});
