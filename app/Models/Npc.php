<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Npc extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'role',
        'description',
        'personality',
        'motivation',
        'voice_description',
        'speech_patterns',
        'catchphrases',
        'stats',
        'faction_id',
        'location_id',
        'is_alive',
    ];

    protected function casts(): array
    {
        return [
            'catchphrases' => 'array',
            'stats' => 'array',
            'is_alive' => 'boolean',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
