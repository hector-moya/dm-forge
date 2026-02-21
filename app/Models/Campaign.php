<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function worldEvents(): HasMany
    {
        return $this->hasMany(WorldEvent::class);
    }

    public function puzzles(): HasMany
    {
        return $this->hasMany(Puzzle::class);
    }

    public function tags(): MorphMany
    {
        return $this->morphMany(Tag::class, 'taggable');
    }

    public function lores(): BelongsToMany
    {
        return $this->belongsToMany(Lore::class, 'campaign_lore');
    }

    public function worldRules(): BelongsToMany
    {
        return $this->belongsToMany(WorldRule::class, 'campaign_world_rule');
    }

    public function specialMechanics(): BelongsToMany
    {
        return $this->belongsToMany(SpecialMechanic::class, 'campaign_special_mechanic');
    }
}
