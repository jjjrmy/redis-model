<?php

namespace Alvin0\RedisModel\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Alvin0\RedisModel\Collection as RedisCollection;
use Illuminate\Support\Collection as BaseCollection;

class HasMany extends Relation
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
     * The keys for eager loading.
     *
     * @var array
     */
    protected $eagerKeys = [];

    /**
     * Create a new has many relationship instance.
     *
     * @param  mixed  $query
     * @param  \Illuminate\Database\Eloquent\Model|\Alvin0\RedisModel\Model  $parent
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return void
     */
    public function __construct($query, $parent, $foreignKey, $localKey)
    {
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
        $this->related = $query->getModel();

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
            $parentKey = $this->getParentKey();
            
            if (!is_null($parentKey)) {
                if ($this->related instanceof RedisModel) {
                    $this->query->where($this->foreignKey, $parentKey);
                } else {
                    $this->query->where($this->foreignKey, '=', $parentKey);
                }
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
        $keys = collect($models)->map(function ($model) {
            return $model->{$this->localKey};
        })->all();

        $this->eagerKeys = $keys;

        if (!($this->parent instanceof EloquentModel && $this->related instanceof RedisModel)) {
            $this->query->whereIn($this->foreignKey, $keys);
        }
    }

    /**
     * Get the results of the relationship.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getResults()
    {
        $parentKey = $this->getParentKey();
        
        if (is_null($parentKey)) {
            return new Collection();
        }

        if ($this->parent instanceof EloquentModel && $this->related instanceof RedisModel) {
            return $this->related::where($this->foreignKey, $parentKey)->get();
        }

        if ($this->related instanceof RedisModel) {
            return $this->related::where($this->foreignKey, $parentKey)->get();
        }

        return $this->query->get();
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
            $model->setRelation($relation, new Collection());
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array  $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            $key = $model->{$this->localKey};
            
            if (isset($dictionary[$key])) {
                $model->setRelation($relation, new Collection($dictionary[$key]));
            }
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @return array
     */
    protected function buildDictionary(Collection $results)
    {
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[$result->{$this->foreignKey}][] = $result;
        }

        return $dictionary;
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
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|\Alvin0\RedisModel\Collection
     */
    public function get($columns = ['*'])
    {
        $results = $this->getResults();
        
        if ($this->related instanceof RedisModel) {
            return $results instanceof RedisCollection ? $results : new RedisCollection($results);
        }

        return $results instanceof Collection ? $results : new Collection($results);
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
            
            foreach ($this->eagerKeys as $key) {
                $records = $this->query->where($this->foreignKey, $key)->get();
                foreach ($records as $record) {
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