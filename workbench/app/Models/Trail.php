<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Alvin0\RedisModel\Model;

class Trail extends Model
{
    /**
     * The model's sub keys for the model.
     *
     * @var array
     */
    protected $subKeys = [
        'mountain_id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'mountain_id',
    ];

    public function mountain()
    {
        return $this->belongsTo(Mountain::class);
    }

    public function skiLift()
    {
        return $this->hasOne(SkiLift::class, 'starting_trail_id');
    }
} 
