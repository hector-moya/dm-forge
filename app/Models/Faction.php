<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'relationships' => 'array',
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

    public function npcs(): HasMany
    {
        return $this->hasMany(Npc::class);
    }

    public function worldEvents(): HasMany
    {
        return $this->hasMany(WorldEvent::class);
    }
}
