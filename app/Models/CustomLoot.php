<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomLoot extends Model
{
    use HasFactory;

    protected $table = 'custom_loot';

    protected $fillable = [
        'name',
        'category',
        'rarity',
        'description',
        'value_gp',
        'weight',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'value_gp' => 'float',
            'weight' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where('name', 'like', "%{$term}%");
    }
}
