<?php

namespace App\Models;

use Alvin0\RedisModel\Model;

class LiftChair extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_occupied' => 'boolean',
        'last_maintenance' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'number',
        'status',
        'is_occupied',
        'last_maintenance',
    ];
} 