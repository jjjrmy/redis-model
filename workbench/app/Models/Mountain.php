<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Alvin0\RedisModel\Traits\HasRedisRelationships;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mountain extends Model
{
    use HasRedisRelationships;

    protected $guarded = [];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function skiLifts(): HasMany
    {
        return $this->hasMany(SkiLift::class);
    }

    public function trails(): HasMany
    {
        return $this->hasMany(Trail::class);
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(Instructor::class);
    }

    public function operators(): BelongsToMany
    {
        return $this->belongsToMany(Operator::class);
    }
} 
