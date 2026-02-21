<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialMechanicRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'special_mechanic_id',
        'name',
        'description',
        'notes',
    ];

    public function specialMechanic(): BelongsTo
    {
        return $this->belongsTo(SpecialMechanic::class);
    }
}
