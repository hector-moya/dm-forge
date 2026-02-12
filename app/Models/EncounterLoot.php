<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EncounterLoot extends Model
{
    protected $table = 'encounter_loot';

    protected $fillable = [
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

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function lootable(): MorphTo
    {
        return $this->morphTo();
    }
}
