<?php

namespace Alvin0\RedisModel;

use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Alvin0\RedisModel\Traits\QueriesRedisRelationships;
use Illuminate\Database\Eloquent\Concerns\QueriesRelationships;

class EloquentBuilder extends BaseBuilder
{
    use QueriesRelationships, QueriesRedisRelationships {
        QueriesRedisRelationships::whereBelongsTo insteadof QueriesRelationships;
    }
} 
