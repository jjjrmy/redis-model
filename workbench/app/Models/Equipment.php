<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Alvin0\RedisModel\Traits\HasRedisRelationships;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Equipment extends Model
{
    use HasRedisRelationships;

    protected $guarded = [];

    public function rental(): HasOne
    {
        return $this->hasOne(Rental::class);
    }
} 