<?php

namespace App\Models;

use Alvin0\RedisModel\Model;

class Lesson extends Model
{
    protected $subKeys = [
        'instructor_id',
        'customer_id',
    ];

    protected $fillable = [
        'id',
        'instructor_id',
        'customer_id',
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
} 