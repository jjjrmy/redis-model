<?php

namespace App\Models;

use Alvin0\RedisModel\Model;

class Brand extends Model
{
    protected $subKeys = [
        'equipment_id',
    ];

    protected $fillable = [
        'name',
        'equipment_id',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
} 