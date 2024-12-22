<?php

namespace App\Models;

use Alvin0\RedisModel\Model;

class Ticket extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
        'price' => 'float',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'valid_from',
        'valid_until',
        'price',
        'is_active',
    ];
} 