<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property string|null $image_path
 */
class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'region',
        'tags',
        'parent_location_id',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_location_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_location_id');
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
