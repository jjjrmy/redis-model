<?php

namespace Workbench\App\Models;

use Alvin0\RedisModel\Model;

class Brand extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'founded_year' => 'integer',
        'product_categories' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'website',
        'founded_year',
        'product_categories',
        'is_active',
        'contact_info',
    ];
} 