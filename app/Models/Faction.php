<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'alignment',
        'goals',
        'resources',
        'relationships',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'relationships' => 'array',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function npcs(): HasMany
    {
        return $this->hasMany(Npc::class);
    }

    public function worldEvents(): HasMany
    {
        return $this->hasMany(WorldEvent::class);
    }
}
