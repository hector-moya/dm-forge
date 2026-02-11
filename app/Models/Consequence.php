<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'description',
        'resolved',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function branchOption(): BelongsTo
    {
        return $this->belongsTo(BranchOption::class);
    }
}
