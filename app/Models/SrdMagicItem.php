<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SrdMagicItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'variant' => 'boolean',
        ];
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where('name', 'like', "%{$term}%");
    }

    public function scopeByRarity(Builder $query, string $rarity): Builder
    {
        return $query->where('rarity', $rarity);
    }
}
