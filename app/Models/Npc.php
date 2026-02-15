<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'catchphrases' => 'array',
            'stats' => 'array',
            'is_alive' => 'boolean',
        ];
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : null);
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

    public function worldEvents(): HasMany
    {
        return $this->hasMany(WorldEvent::class);
    }
}
