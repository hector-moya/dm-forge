<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Scene extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'sort_order',
        'is_revealed',
        'notes',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_revealed' => 'boolean',
        ];
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : null);
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

    public function loot(): HasMany
    {
        return $this->hasMany(SceneLoot::class);
    }

    public function incomingBranches(): HasMany
    {
        return $this->hasMany(BranchOption::class, 'destination_scene_id');
    }

    public function puzzles(): HasMany
    {
        return $this->hasMany(Puzzle::class);
    }
}
