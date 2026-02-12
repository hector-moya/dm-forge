<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SrdEquipment extends Model
{
    protected $table = 'srd_equipment';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'damage' => 'array',
            'two_handed_damage' => 'array',
            'range' => 'array',
            'armor_class' => 'array',
            'properties' => 'array',
            'special' => 'array',
            'cost_gp' => 'float',
            'weight' => 'float',
        ];
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where('name', 'like', "%{$term}%");
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('equipment_category', $category);
    }
}
