<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameSession extends Model
{
    use HasFactory;

    protected $table = 'game_sessions';

    protected $fillable = [
        'title',
        'session_number',
        'type',
        'status',
        'setup_text',
        'recap_text',
        'dm_notes',
        'generated_narrative',
        'generated_bullets',
        'generated_hooks',
        'generated_world_state',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'session_number' => 'integer',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function scenes(): HasMany
    {
        return $this->hasMany(Scene::class);
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }

    public function branchOptions(): HasMany
    {
        return $this->hasMany(BranchOption::class);
    }

    public function sessionLogs(): HasMany
    {
        return $this->hasMany(SessionLog::class);
    }

    public function alignmentEvents(): HasMany
    {
        return $this->hasMany(AlignmentEvent::class);
    }
}
