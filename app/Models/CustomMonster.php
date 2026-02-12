<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomMonster extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'size',
        'type',
        'alignment',
        'armor_class',
        'hit_points',
        'hit_dice',
        'speed',
        'strength',
        'dexterity',
        'constitution',
        'intelligence',
        'wisdom',
        'charisma',
        'challenge_rating',
        'xp',
        'special_abilities',
        'actions',
        'legendary_actions',
        'senses',
        'languages',
        'damage_vulnerabilities',
        'damage_resistances',
        'damage_immunities',
        'condition_immunities',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'speed' => 'array',
            'special_abilities' => 'array',
            'actions' => 'array',
            'legendary_actions' => 'array',
            'senses' => 'array',
            'damage_vulnerabilities' => 'array',
            'damage_resistances' => 'array',
            'damage_immunities' => 'array',
            'condition_immunities' => 'array',
            'armor_class' => 'integer',
            'hit_points' => 'integer',
            'challenge_rating' => 'float',
            'xp' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where('name', 'like', "%{$term}%");
    }
}
