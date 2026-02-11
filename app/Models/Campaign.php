<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'premise',
        'lore',
        'world_rules',
        'theme_tone',
        'special_mechanics',
        'status',
        'bible_cache',
    ];

    protected function casts(): array
    {
        return [
            'special_mechanics' => 'array',
            'status' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    public function factions(): HasMany
    {
        return $this->hasMany(Faction::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function npcs(): HasMany
    {
        return $this->hasMany(Npc::class);
    }

    public function tags(): MorphMany
    {
        return $this->morphMany(Tag::class, 'taggable');
    }
}
