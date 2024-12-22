<?php

namespace App\Models;

use Alvin0\RedisModel\Model;

class Location extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id', // State (like "CA")
        'name', // Full name (like "California")
    ];

    public function mountain()
    {
        return $this->hasOne(Mountain::class);
    }
} 
