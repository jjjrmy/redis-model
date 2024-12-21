<?php

namespace Alvin0\RedisModel\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Alvin0\RedisModel\Collection as RedisCollection;
use Illuminate\Support\Collection as BaseCollection;

class BelongsTo extends Relation
{
    /**
     * The child model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model|\Alvin0\RedisModel\Model
     */
    protected $child;

    /**
     * The foreign key of the relationship.
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * The associated key on the parent model.
     *
     * @var string
     */
    protected $ownerKey;

    /**
     * The keys for eager loading.
     *
     * @var array
     */
    protected $eagerKeys = [];

    /**
     * Create a new belongs to relationship instance.
     *
     * @param  mixed  $query
     * @param  \Illuminate\Database\Eloquent\Model|\Alvin0\RedisModel\Model  $child
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @return void
     */
    public function __construct($query, $child, $foreignKey, $ownerKey)
    {
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
        $this->child = $child;

        parent::__construct($query, $child);
        
        $this->related = $query->getModel();
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            $this->query->where($this->ownerKey, '=', $this->child->{$this->foreignKey});
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
            return $model->{$this->foreignKey};
        })->filter()->values()->all();

        if (!($this->child instanceof EloquentModel && $this->related instanceof RedisModel)) {
            $this->query->whereIn($this->ownerKey, $keys);
        }
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        if (is_null($this->child->{$this->foreignKey})) {
            return null;
        }

        if ($this->child instanceof EloquentModel && $this->related instanceof RedisModel) {
            return $this->related::where($this->ownerKey, $this->child->{$this->foreignKey})->first();
        }

        if ($this->related instanceof RedisModel) {
            return $this->related::where($this->ownerKey, $this->child->{$this->foreignKey})->first();
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
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            if (isset($dictionary[$model->{$this->foreignKey}])) {
                $model->setRelation($relation, $dictionary[$model->{$this->foreignKey}]);
            }
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's owner key.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @return array
     */
    protected function buildDictionary(Collection $results)
    {
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[$result->{$this->ownerKey}] = $result;
        }

        return $dictionary;
    }

    /**
     * Get the results of the relationship as a collection.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|\Alvin0\RedisModel\Collection
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
            
            foreach ($this->eagerKeys as $key) {
                if ($record = $this->related::where($this->ownerKey, $key)->first()) {
                    $results->push($record);
                }
            }

            return $results;
        }

        return $this->get();
    }
} 