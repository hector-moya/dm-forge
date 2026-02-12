<?php

namespace App\DataTransferObjects;

use App\Enums\DifficultyRating;

class EncounterDifficulty
{
    /**
     * @param  array{easy: int, medium: int, hard: int, deadly: int}  $partyThresholds
     */
    public function __construct(
        public DifficultyRating $rating,
        public int $adjustedXp,
        public int $rawXp,
        public float $multiplier,
        public array $partyThresholds,
    ) {}
}
