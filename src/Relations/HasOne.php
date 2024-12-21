<?php

namespace Alvin0\RedisModel\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Alvin0\RedisModel\Collection as RedisCollection;
use Illuminate\Support\Collection as BaseCollection;

class HasOne extends Relation
{
    /**
     * The foreign key of the relationship.
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * The local key of the parent model.
     *
     * @var string
     */
    protected $localKey;

    /**
     * The related model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model|\Alvin0\RedisModel\Model
     */
    protected $related;

    /**
     * The keys for eager loading.
     *
     * @var array
     */
    protected $eagerKeys = [];

    /**
     * Create a new has one relationship instance.
     *
     * @param  mixed  $query
     * @param  \Illuminate\Database\Eloquent\Model|\Alvin0\RedisModel\Model  $parent
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return void
     */
    public function __construct($query, $parent, $foreignKey, $localKey)
    {
        $this->localKey = $localKey;
        $this->foreignKey = $foreignKey;

        parent::__construct($query, $parent);
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            if (!($this->related instanceof RedisModel)) {
                $this->query->where($this->foreignKey, $this->getParentKey());
            }
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $this->eagerKeys = collect($models)->map(function ($model) {
            return $model->{$this->localKey};
        })->all();

        if (!($this->related instanceof RedisModel)) {
            $this->query->whereIn($this->foreignKey, $this->eagerKeys);
        }
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        $parentKey = $this->getParentKey();
        
        if (is_null($parentKey)) {
            return null;
        }

        if ($this->related instanceof RedisModel) {
            return $this->related::firstWhere($this->getForeignKeyName(), $parentKey);
        }

        return $this->query->first();
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array  $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array  $models
     * @param  \Illuminate\Database\Eloquent\Collection|\Alvin0\RedisModel\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            $key = $model->{$this->localKey};

            if (isset($dictionary[$key])) {
                $model->setRelation($relation, $dictionary[$key]);
            }
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @param  \Illuminate\Database\Eloquent\Collection|\Alvin0\RedisModel\Collection  $results
     * @return array
     */
    protected function buildDictionary($results)
    {
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[$result->{$this->foreignKey}] = $result;
        }

        return $dictionary;
    }

    /**
     * Get the plain foreign key name without table prefix.
     *
     * @return string
     */
    public function getForeignKeyName()
    {
        $segments = explode('.', $this->foreignKey);
        return end($segments);
    }

    /**
     * Get the parent key value.
     *
     * @return mixed
     */
    protected function getParentKey()
    {
        return $this->parent->getAttribute($this->localKey);
    }

    /**
     * Get the results of the relationship as a collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function get($columns = ['*'])
    {
        $result = $this->getResults();
        
        if ($this->related instanceof RedisModel) {
            return new RedisCollection(is_null($result) ? [] : [$result]);
        }

        return new Collection(is_null($result) ? [] : [$result]);
    }

    /**
     * Get the relationship for eager loading.
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Alvin0\RedisModel\Collection
     */
    public function getEager()
    {
        if ($this->related instanceof RedisModel) {
            $results = new RedisCollection();
            $records = $this->related::all();
            
            foreach ($records as $record) {
                if (in_array($record->{$this->getForeignKeyName()}, $this->eagerKeys)) {
                    $results->push($record);
                }
            }

            return $results;
        }

        return $this->get();
    }

    /**
     * Create a new instance of the related model.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|\Alvin0\RedisModel\Model
     */
    public function create(array $attributes = [])
    {
        $instance = $this->related->newInstance($attributes);
        $foreignKey = str_replace($this->related->getTable().'.', '', $this->foreignKey);
        $instance->setAttribute($foreignKey, $this->getParentKey());
        $instance->save();

        return $instance;
    }
} 