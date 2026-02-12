<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncounterMonster extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hp_max',
        'hp_current',
        'armor_class',
        'initiative',
        'stats',
        'conditions',
        'notes',
        'sort_order',
        'srd_monster_id',
        'custom_monster_id',
        'challenge_rating',
        'xp',
    ];

    protected function casts(): array
    {
        return [
            'hp_max' => 'integer',
            'hp_current' => 'integer',
            'armor_class' => 'integer',
            'initiative' => 'integer',
            'stats' => 'array',
            'conditions' => 'array',
            'sort_order' => 'integer',
            'challenge_rating' => 'float',
            'xp' => 'integer',
        ];
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function srdMonster(): BelongsTo
    {
        return $this->belongsTo(SrdMonster::class);
    }

    public function customMonster(): BelongsTo
    {
        return $this->belongsTo(CustomMonster::class);
    }
}
