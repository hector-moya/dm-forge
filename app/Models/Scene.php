<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scene extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'sort_order',
        'is_revealed',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_revealed' => 'boolean',
        ];
    }

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }

    public function branchOptions(): HasMany
    {
        return $this->hasMany(BranchOption::class);
    }
}
