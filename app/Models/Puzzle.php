<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Puzzle extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'scene_id',
        'name',
        'description',
        'solution',
        'hint_tier_1',
        'hint_tier_2',
        'hint_tier_3',
        'difficulty',
        'puzzle_type',
        'is_solved',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_solved' => 'boolean',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }
}
