<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlignmentEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'action_description',
        'tags',
        'good_evil_delta',
        'law_chaos_delta',
        'ai_suggested_ge',
        'ai_suggested_lc',
        'dm_overridden',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'good_evil_delta' => 'integer',
            'law_chaos_delta' => 'integer',
            'ai_suggested_ge' => 'integer',
            'ai_suggested_lc' => 'integer',
            'dm_overridden' => 'boolean',
        ];
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }
}
