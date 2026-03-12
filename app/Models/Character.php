<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property array<string, mixed>|null $stats
 */
class Character extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'player_name',
        'class',
        'race',
        'background',
        'level',
        'hp_max',
        'hp_current',
        'armor_class',
        'speed',
        'proficiency_bonus',
        'experience_points',
        'stats',
        'good_evil_score',
        'law_chaos_score',
        'alignment_label',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'stats' => 'array',
            'level' => 'integer',
            'hp_max' => 'integer',
            'hp_current' => 'integer',
            'armor_class' => 'integer',
            'speed' => 'integer',
            'proficiency_bonus' => 'integer',
            'experience_points' => 'integer',
            'good_evil_score' => 'integer',
            'law_chaos_score' => 'integer',
        ];
    }

    public function abilityModifier(string $ability): int
    {
        $score = $this->stats['ability_scores'][$ability] ?? 10;

        return (int) floor(($score - 10) / 2);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function alignmentEvents(): HasMany
    {
        return $this->hasMany(AlignmentEvent::class);
    }
}
