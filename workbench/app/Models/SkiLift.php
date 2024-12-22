<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Alvin0\RedisModel\Traits\HasRedisRelationships;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkiLift extends Model
{
    use HasRedisRelationships;

    protected $guarded = [];

    public function mountain()
    {
        return $this->belongsTo(Mountain::class);
    }

    public function startingTrail()
    {
        return $this->belongsTo(Trail::class, 'starting_trail_id');
    }

    public function endingTrail()
    {
        return $this->belongsTo(Trail::class, 'ending_trail_id');
    }

    public function operator()
    {
        return $this->hasOne(Operator::class);
    }
} 
