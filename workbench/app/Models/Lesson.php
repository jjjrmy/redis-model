<?php

namespace App\Models;

use Alvin0\RedisModel\Model;

class Lesson extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'price' => 'float',
        'is_private' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'skill_level',
        'start_time',
        'end_time',
        'price',
        'is_private',
        'status',
    ];
} 