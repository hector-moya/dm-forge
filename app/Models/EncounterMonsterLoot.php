<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EncounterMonsterLoot extends Model
{
    protected $table = 'encounter_monster_loot';

    protected $fillable = [
        'encounter_monster_id',
        'lootable_type',
        'lootable_id',
        'quantity',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function encounterMonster(): BelongsTo
    {
        return $this->belongsTo(EncounterMonster::class);
    }

    public function lootable(): MorphTo
    {
        return $this->morphTo();
    }
}
