<?php

namespace App\Services;

use App\DataTransferObjects\EncounterDifficulty;
use App\Enums\DifficultyRating;
use App\Models\Encounter;
use Illuminate\Support\Collection;

class EncounterBalancer
{
    /**
     * XP thresholds per character level for each difficulty tier.
     *
     * @var array<int, array{easy: int, medium: int, hard: int, deadly: int}>
     */
    private const XP_THRESHOLDS = [
        1 => ['easy' => 25, 'medium' => 50, 'hard' => 75, 'deadly' => 100],
        2 => ['easy' => 50, 'medium' => 100, 'hard' => 150, 'deadly' => 200],
        3 => ['easy' => 75, 'medium' => 150, 'hard' => 225, 'deadly' => 400],
        4 => ['easy' => 125, 'medium' => 250, 'hard' => 375, 'deadly' => 500],
        5 => ['easy' => 250, 'medium' => 500, 'hard' => 750, 'deadly' => 1100],
        6 => ['easy' => 300, 'medium' => 600, 'hard' => 900, 'deadly' => 1400],
        7 => ['easy' => 350, 'medium' => 750, 'hard' => 1100, 'deadly' => 1700],
        8 => ['easy' => 450, 'medium' => 900, 'hard' => 1400, 'deadly' => 2100],
        9 => ['easy' => 550, 'medium' => 1100, 'hard' => 1600, 'deadly' => 2400],
        10 => ['easy' => 600, 'medium' => 1200, 'hard' => 1900, 'deadly' => 2800],
        11 => ['easy' => 800, 'medium' => 1600, 'hard' => 2400, 'deadly' => 3600],
        12 => ['easy' => 1000, 'medium' => 2000, 'hard' => 3000, 'deadly' => 4500],
        13 => ['easy' => 1100, 'medium' => 2200, 'hard' => 3400, 'deadly' => 5100],
        14 => ['easy' => 1250, 'medium' => 2500, 'hard' => 3800, 'deadly' => 5700],
        15 => ['easy' => 1400, 'medium' => 2800, 'hard' => 4300, 'deadly' => 6400],
        16 => ['easy' => 1600, 'medium' => 3200, 'hard' => 4800, 'deadly' => 7200],
        17 => ['easy' => 2000, 'medium' => 3900, 'hard' => 5900, 'deadly' => 8800],
        18 => ['easy' => 2100, 'medium' => 4200, 'hard' => 6300, 'deadly' => 9500],
        19 => ['easy' => 2400, 'medium' => 4900, 'hard' => 7300, 'deadly' => 10900],
        20 => ['easy' => 2800, 'medium' => 5700, 'hard' => 8500, 'deadly' => 12700],
    ];

    /**
     * Calculate the difficulty of an encounter for a given party.
     *
     * @param  Collection<int, \App\Models\Character>  $characters
     */
    public function calculate(Encounter $encounter, Collection $characters): EncounterDifficulty
    {
        $partyThresholds = $this->sumPartyThresholds($characters);
        $monsterCount = $encounter->monsters()->count();
        $rawXp = (int) $encounter->monsters()->sum('xp');
        $multiplier = $this->getMultiplier($monsterCount, $characters->count());
        $adjustedXp = (int) round($rawXp * $multiplier);
        $rating = $this->determineRating($adjustedXp, $partyThresholds);

        return new EncounterDifficulty(
            rating: $rating,
            adjustedXp: $adjustedXp,
            rawXp: $rawXp,
            multiplier: $multiplier,
            partyThresholds: $partyThresholds,
        );
    }

    /**
     * Get the encounter multiplier based on monster count and party size.
     */
    public function getMultiplier(int $monsterCount, int $partySize = 4): float
    {
        if ($monsterCount === 0) {
            return 1.0;
        }

        $baseMultiplier = match (true) {
            $monsterCount >= 15 => 4.0,
            $monsterCount >= 11 => 3.0,
            $monsterCount >= 7 => 2.5,
            $monsterCount >= 3 => 2.0,
            $monsterCount >= 2 => 1.5,
            default => 1.0,
        };

        // Adjust for party size (fewer than 3 = harder, more than 5 = easier)
        if ($partySize < 3) {
            return $this->adjustMultiplierUp($baseMultiplier);
        }

        if ($partySize > 5) {
            return $this->adjustMultiplierDown($baseMultiplier);
        }

        return $baseMultiplier;
    }

    /**
     * @param  Collection<int, \App\Models\Character>  $characters
     * @return array{easy: int, medium: int, hard: int, deadly: int}
     */
    private function sumPartyThresholds(Collection $characters): array
    {
        $thresholds = ['easy' => 0, 'medium' => 0, 'hard' => 0, 'deadly' => 0];

        foreach ($characters as $character) {
            $level = min(max((int) $character->level, 1), 20);
            $levelThresholds = self::XP_THRESHOLDS[$level];

            $thresholds['easy'] += $levelThresholds['easy'];
            $thresholds['medium'] += $levelThresholds['medium'];
            $thresholds['hard'] += $levelThresholds['hard'];
            $thresholds['deadly'] += $levelThresholds['deadly'];
        }

        return $thresholds;
    }

    /**
     * @param  array{easy: int, medium: int, hard: int, deadly: int}  $partyThresholds
     */
    private function determineRating(int $adjustedXp, array $partyThresholds): DifficultyRating
    {
        if ($adjustedXp === 0) {
            return DifficultyRating::Trivial;
        }

        if ($adjustedXp >= $partyThresholds['deadly']) {
            return DifficultyRating::Deadly;
        }

        if ($adjustedXp >= $partyThresholds['hard']) {
            return DifficultyRating::Hard;
        }

        if ($adjustedXp >= $partyThresholds['medium']) {
            return DifficultyRating::Medium;
        }

        if ($adjustedXp >= $partyThresholds['easy']) {
            return DifficultyRating::Easy;
        }

        return DifficultyRating::Trivial;
    }

    private function adjustMultiplierUp(float $multiplier): float
    {
        $tiers = [1.0, 1.5, 2.0, 2.5, 3.0, 4.0, 5.0];
        $index = array_search($multiplier, $tiers);

        if ($index !== false && $index < count($tiers) - 1) {
            return $tiers[$index + 1];
        }

        return $multiplier;
    }

    private function adjustMultiplierDown(float $multiplier): float
    {
        $tiers = [0.5, 1.0, 1.5, 2.0, 2.5, 3.0, 4.0];
        $index = array_search($multiplier, $tiers);

        if ($index !== false && $index > 0) {
            return $tiers[$index - 1];
        }

        return $multiplier;
    }
}
