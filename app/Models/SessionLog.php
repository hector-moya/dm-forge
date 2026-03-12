<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \Carbon\Carbon|null $logged_at
 */
class SessionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'scene_id',
        'entry',
        'type',
        'character_ids',
        'tags',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'character_ids' => 'array',
            'tags' => 'array',
            'logged_at' => 'datetime',
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
}
