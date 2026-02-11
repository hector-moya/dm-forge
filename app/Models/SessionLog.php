<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry',
        'type',
        'tags',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'logged_at' => 'datetime',
        ];
    }

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }
}
