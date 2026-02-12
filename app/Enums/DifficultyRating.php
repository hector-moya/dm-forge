<?php

namespace App\Enums;

enum DifficultyRating: string
{
    case Trivial = 'trivial';
    case Easy = 'easy';
    case Medium = 'medium';
    case Hard = 'hard';
    case Deadly = 'deadly';

    public function label(): string
    {
        return match ($this) {
            self::Trivial => 'Trivial',
            self::Easy => 'Easy',
            self::Medium => 'Medium',
            self::Hard => 'Hard',
            self::Deadly => 'Deadly',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Trivial => 'zinc',
            self::Easy => 'green',
            self::Medium => 'amber',
            self::Hard => 'orange',
            self::Deadly => 'red',
        };
    }
}
