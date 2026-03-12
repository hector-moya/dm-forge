<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property string|null $image_path
 */
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
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'value_gp' => 'float',
            'weight' => 'float',
        ];
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : null);
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
