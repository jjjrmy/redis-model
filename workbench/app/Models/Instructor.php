<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Alvin0\RedisModel\Traits\HasRedisRelationships;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Instructor extends Model
{
    use HasRedisRelationships;

    protected $guarded = [];

    public function mountain(): BelongsTo
    {
        return $this->belongsTo(Mountain::class);
    }
} 
