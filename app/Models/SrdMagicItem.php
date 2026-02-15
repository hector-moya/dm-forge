<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class SrdMagicItem extends Model
{
    private const IMAGE_BASE_URL = 'https://www.dnd5eapi.co';

    protected $guarded = [];

    protected function fullImageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image_url
            ? self::IMAGE_BASE_URL.$this->image_url
            : null
        );
    }

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
