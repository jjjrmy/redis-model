<?php

namespace App\Models;

use Alvin0\RedisModel\Model;

class Trail extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_open' => 'boolean',
        'conditions' => 'array',
        'last_groomed' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'difficulty',
        'length',
        'is_open',
        'conditions',
        'last_groomed',
    ];
} 