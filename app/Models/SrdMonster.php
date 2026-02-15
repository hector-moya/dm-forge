<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class SrdMonster extends Model
{
    private const IMAGE_BASE_URL = 'https://www.dnd5eapi.co';

    protected $guarded = [];

    protected function fullImageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image_url
            ? self::IMAGE_BASE_URL.$this->image_url
            : null
        );
    }

    protected function casts(): array
    {
        return [
            'speed' => 'array',
            'proficiencies' => 'array',
            'damage_vulnerabilities' => 'array',
            'damage_resistances' => 'array',
            'damage_immunities' => 'array',
            'condition_immunities' => 'array',
            'senses' => 'array',
            'special_abilities' => 'array',
            'actions' => 'array',
            'legendary_actions' => 'array',
            'reactions' => 'array',
            'armor_class' => 'integer',
            'hit_points' => 'integer',
            'strength' => 'integer',
            'dexterity' => 'integer',
            'constitution' => 'integer',
            'intelligence' => 'integer',
            'wisdom' => 'integer',
            'charisma' => 'integer',
            'challenge_rating' => 'float',
            'xp' => 'integer',
        ];
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where('name', 'like', "%{$term}%");
    }

    public function scopeByChallengeRating(Builder $query, float $cr): Builder
    {
        return $query->where('challenge_rating', $cr);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
