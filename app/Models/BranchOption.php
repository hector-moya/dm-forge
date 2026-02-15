<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BranchOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'description',
        'sort_order',
        'chosen',
        'scene_id',
        'game_session_id',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'chosen' => 'boolean',
        ];
    }

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }

    public function consequences(): HasMany
    {
        return $this->hasMany(Consequence::class);
    }
}
