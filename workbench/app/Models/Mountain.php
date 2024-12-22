<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Alvin0\RedisModel\Traits\HasRedisRelationships;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mountain extends Model
{
    use HasRedisRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'location_id',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
} 