<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Alvin0\RedisModel\Traits\HasRedisRelationships;

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
        'elevation',
        'description',
    ];
} 