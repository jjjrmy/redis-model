<?php

namespace App\Models;

use Alvin0\RedisModel\Model;

class Company extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
    ];

    public function mountain()
    {
        return $this->hasOne(Mountain::class);
    }
} 
