<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncounterNpc extends Model
{
    /** @use HasFactory<\Database\Factories\EncounterNpcFactory> */
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
        'npc_id',
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
        ];
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class);
    }
}
