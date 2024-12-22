<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Alvin0\RedisModel\Model;

class Rental extends Model
{
    protected $subKeys = [
        'customer_id',
        'equipment_id',
    ];

    protected $fillable = [
        'id',
        'customer_id',
        'equipment_id',
        'start_date',
        'end_date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
} 