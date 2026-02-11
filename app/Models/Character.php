<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Character extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'player_name',
        'class',
        'level',
        'hp_max',
        'hp_current',
        'armor_class',
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
            'good_evil_score' => 'integer',
            'law_chaos_score' => 'integer',
        ];
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
