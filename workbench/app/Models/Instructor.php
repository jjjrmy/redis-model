<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Alvin0\RedisModel\Traits\HasRedisRelationships;

class Instructor extends Model
{
    use HasRedisRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'certification_level',
        'specialties',
        'status',
    ];
} 