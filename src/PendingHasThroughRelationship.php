<?php

namespace Alvin0\RedisModel;

use Illuminate\Database\Eloquent\Model;
use Alvin0\RedisModel\Model as RedisModel;
use Alvin0\RedisModel\Relations\HasOneThrough;

class PendingHasThroughRelationship
{
    /**
     * The model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model|\Alvin0\RedisModel\Model
     */
    protected $model;

    /**
     * The through relationship instance.
     *
     * @var \Illuminate\Database\Eloquent\Relations\HasOneOrManyThrough|\Alvin0\RedisModel\Relations\HasOneThrough
     */
    protected $throughRelation;

    /**
     * Create a new pending has-through relationship instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Alvin0\RedisModel\Model  $model
     * @param  \Illuminate\Database\Eloquent\Relations\HasOneOrManyThrough|\Alvin0\RedisModel\Relations\HasOneThrough  $throughRelation
     * @return void
     */
    public function __construct($model, $throughRelation)
    {
        $this->model = $model;
        $this->throughRelation = $throughRelation;
    }

    /**
     * Define the target relationship.
     *
     * @param  string  $relationship
     * @return mixed
     */
    public function has($relationship)
    {
        if ($this->throughRelation instanceof HasOneThrough) {
            return $this->throughRelation->has($relationship);
        }

        $through = $this->throughRelation->first();
        if (!$through) {
            return null;
        }

        $relation = $through->$relationship();
        return $relation->getResults();
    }

    /**
     * Handle dynamic method calls into the relationship.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (str_starts_with($method, 'has')) {
            return $this->has(lcfirst(substr($method, 3)));
        }

        return $this->throughRelation->$method(...$parameters);
    }
} 