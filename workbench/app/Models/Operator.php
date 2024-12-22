<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Alvin0\RedisModel\Traits\HasRedisRelationships;

class Operator extends Model
{
    use HasRedisRelationships;

    protected $guarded = [];

    public function lift()
    {
        return $this->belongsTo(SkiLift::class);
    }
} 