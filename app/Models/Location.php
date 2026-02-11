<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'region',
        'tags',
        'parent_location_id',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
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
}
