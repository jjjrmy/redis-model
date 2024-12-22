<?php

namespace App\Models;

use Alvin0\RedisModel\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiftChair extends Model
{
    protected $subKeys = [
        'lift_id',
    ];

    protected $fillable = [
        'id',
        'lift_id',
    ];

    public function lift(): BelongsTo
    {
        return $this->belongsTo(SkiLift::class);
    }
} 
