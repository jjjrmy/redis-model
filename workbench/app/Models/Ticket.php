<?php

namespace App\Models;

use Alvin0\RedisModel\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasManyThrough, HasOneThrough};

class Ticket extends Model
{
    /**
     * The model's sub keys for the model.
     *
     * @var array
     */
    protected $subKeys = [
        'customer_id',
        'mountain_id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'mountain_id',  
        'valid_from',
        'valid_until',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function mountain(): BelongsTo
    {
        return $this->belongsTo(Mountain::class);
    }

    public function lifts(): HasManyThrough
    {
        return $this->hasManyThrough(SkiLift::class, Mountain::class);
    }
} 
