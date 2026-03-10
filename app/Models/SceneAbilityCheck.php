<?php

namespace App\Models;

use App\Enums\DndSkill;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SceneAbilityCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'scene_id',
        'skill',
        'subject',
        'dc',
        'dc_super',
        'failure_text',
        'success_text',
        'super_success_text',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'skill' => DndSkill::class,
            'dc' => 'integer',
            'dc_super' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }
}
