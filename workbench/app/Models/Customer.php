<?php

namespace App\Models;

use Alvin0\RedisModel\Model;

class Customer extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The model's sub keys for the model.
     *
     * @var array
     */
    protected $subKeys = ['email'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
    ];

    public function rental()
    {
        return $this->hasOne(Rental::class);
    }
} 