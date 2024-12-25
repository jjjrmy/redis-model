<?php

namespace Alvin0\RedisModel\Relations;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Alvin0\RedisModel\Model as RedisModel;
use Alvin0\RedisModel\Collection as RedisCollection;

class HasManyThrough extends Relation
{
    /**
     * The "through" parent model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model|\Alvin0\RedisModel\Model
     */
    protected $throughParent;

    /**
     * The far parent model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model|\Alvin0\RedisModel\Model
     */
    protected $farParent;

    /**
     * The near key on the relationship.
     *
     * @var string
     */
    protected $firstKey;

    /**
     * The far key on the relationship.
     *
     * @var string
     */
    protected $secondKey;

    /**
     * The local key on the relationship.
     *
     * @var string
     */
    protected $localKey;

    /**
     * The local key on the intermediary model.
     *
     * @var string
     */
    protected $secondLocalKey;

    /**
     * The eager keys on the relationship.
     *
     * @var array
     */
    protected $eagerKeys = [];

    /**
     * Create a new has many through relationship instance.
     *
     * @param  mixed  $query
     * @param  \Illuminate\Database\Eloquent\Model|\Alvin0\RedisModel\Model  $farParent
     * @param  \Illuminate\Database\Eloquent\Model|\Alvin0\RedisModel\Model  $throughParent
     * @param  string  $firstKey
     * @param  string  $secondKey
     * @param  string  $localKey
     * @param  string  $secondLocalKey
     * @return void
     */
    public function __construct($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey)
    {
        $this->localKey = $localKey;
        $this->secondKey = $secondKey;
        $this->firstKey = $firstKey;
        $this->secondLocalKey = $secondLocalKey;
        $this->farParent = $farParent;
        $this->throughParent = $throughParent;

        parent::__construct($query, $farParent);

        $this->related = $query->getModel();
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (!static::$constraints) {
            return;
        }

        // Get the intermediate models first
        if ($this->throughParent instanceof RedisModel) {
            $throughModels = collect();
            if ($through = $this->throughParent::where($this->firstKey, (string)$this->farParent->{$this->localKey})->first()) {
                $throughModels->push($through);
            }
        } else {
            $throughModels = $this->throughParent::where($this->firstKey, $this->farParent->{$this->localKey})->get();
        }

        if ($throughModels->isNotEmpty()) {
            $throughKeys = $throughModels->pluck($this->secondLocalKey)->filter()->values()->all();
            
            if ($this->related instanceof RedisModel) {
                // For Redis related model, we need to handle each key separately
                $this->eagerKeys = $throughKeys;
            } else {
                $this->query->whereIn($this->secondKey, $throughKeys);
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
        })->filter()->values()->all();

        // Store the parent keys for later use
        $this->eagerKeys = $keys;

        if ($this->throughParent instanceof RedisModel) {
            // For Redis through model, we need to handle each model separately
            $throughModels = collect();
            foreach ($keys as $key) {
                if ($through = $this->throughParent::where($this->firstKey, (string)$key)->first()) {
                    $throughModels->push($through);
                }
            }
            
            // Store the through models' IDs for later use
            $throughKeys = $throughModels->pluck($this->secondLocalKey)->filter()->values()->all();
            
            if (!($this->related instanceof RedisModel)) {
                $this->query->whereIn($this->secondKey, $throughKeys);
            }
        } else {
            // For Eloquent through model
            $throughModels = $this->throughParent::whereIn($this->firstKey, $keys)->get();
            $throughKeys = $throughModels->pluck($this->secondLocalKey)->filter()->values()->all();
            
            if ($this->related instanceof RedisModel) {
                // For Redis related model, we need to handle each key separately
                $this->eagerKeys = $throughKeys;
            } else {
                $this->query->whereIn($this->secondKey, $throughKeys);
            }
        }
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
            if ($this->related instanceof RedisModel) {
                $model->setRelation($relation, new RedisCollection);
            } else {
                $model->setRelation($relation, $this->related->newCollection());
            }
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array  $models
     * @param  \Illuminate\Support\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = [];

        // Convert results to Eloquent Collection if needed
        if (!$results instanceof EloquentCollection) {
            $results = new EloquentCollection($results->all());
        }

        // Build a dictionary of through models first
        $throughModels = [];
        foreach ($models as $model) {
            if ($this->throughParent instanceof RedisModel) {
                $through = $this->throughParent::where($this->firstKey, (string)$model->{$this->localKey})->first();
                if ($through) {
                    $throughModels[$model->{$this->localKey}][] = $through;
                }
            } else {
                $throughs = $this->throughParent::where($this->firstKey, $model->{$this->localKey})->get();
                if ($throughs->isNotEmpty()) {
                    $throughModels[$model->{$this->localKey}] = $throughs->all();
                }
            }
        }

        // Build dictionary of results
        foreach ($results as $result) {
            foreach ($throughModels as $parentKey => $throughs) {
                foreach ($throughs as $through) {
                    if ($this->related instanceof RedisModel) {
                        if ((string)$result->{$this->secondKey} === (string)$through->{$this->secondLocalKey}) {
                            $dictionary[$parentKey][] = $result;
                        }
                    } else {
                        if ($result->{$this->secondKey} == $through->{$this->secondLocalKey}) {
                            $dictionary[$parentKey][] = $result;
                        }
                    }
                }
            }
        }

        // Match results to models
        foreach ($models as $model) {
            if (isset($dictionary[$model->{$this->localKey}])) {
                if ($this->related instanceof RedisModel) {
                    $model->setRelation($relation, new RedisCollection($dictionary[$model->{$this->localKey}]));
                } else {
                    $model->setRelation($relation, $this->related->newCollection($dictionary[$model->{$this->localKey}]));
                }
            }
        }

        return $models;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        // Get the intermediate models first
        if ($this->throughParent instanceof RedisModel) {
            $throughModels = collect();
            if ($through = $this->throughParent::where($this->firstKey, (string)$this->farParent->{$this->localKey})->first()) {
                $throughModels->push($through);
            }
        } else {
            $throughModels = $this->throughParent::where($this->firstKey, $this->farParent->{$this->localKey})->get();
        }

        if ($throughModels->isEmpty()) {
            return $this->related instanceof RedisModel ? new RedisCollection : $this->related->newCollection();
        }

        $throughKeys = $throughModels->pluck($this->secondLocalKey)->filter()->values()->all();

        // Get the final models
        if ($this->related instanceof RedisModel) {
            $results = collect();
            foreach ($throughKeys as $key) {
                if ($result = $this->related::where($this->secondKey, (string)$key)->first()) {
                    $results->push($result);
                }
            }
            return new RedisCollection($results);
        }

        return $this->query->whereIn($this->secondKey, $throughKeys)->get();
    }

    /**
     * Get the results of the relationship as a collection.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|\Alvin0\RedisModel\Collection
     */
    public function get($columns = ['*'])
    {
        return $this->getResults();
    }

    /**
     * Get the eager results for the relationship.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getEager()
    {
        if ($this->throughParent instanceof RedisModel) {
            // For Redis through model, we need to get the through models first
            $throughModels = collect();
            foreach ($this->eagerKeys as $key) {
                if ($through = $this->throughParent::where($this->firstKey, (string)$key)->first()) {
                    $throughModels->push($through);
                }
            }

            // Now get the related models using the through models' IDs
            $results = collect();
            foreach ($throughModels as $through) {
                $throughKey = $through->{$this->secondLocalKey};
                if ($this->related instanceof RedisModel) {
                    if ($result = $this->related::where($this->secondKey, (string)$throughKey)->first()) {
                        $results->push($result);
                    }
                } else {
                    if ($result = $this->query->where($this->secondKey, $throughKey)->first()) {
                        $results->push($result);
                    }
                }
            }
            return $results;
        } else {
            // For Eloquent through model
            $throughModels = $this->throughParent::whereIn($this->firstKey, $this->eagerKeys)->get();
            $throughKeys = $throughModels->pluck($this->secondLocalKey)->filter()->values()->all();

            if ($this->related instanceof RedisModel) {
                $results = collect();
                foreach ($throughKeys as $key) {
                    if ($result = $this->related::where($this->secondKey, (string)$key)->first()) {
                        $results->push($result);
                    }
                }
                return $results;
            } else {
                return $this->query->whereIn($this->secondKey, $throughKeys)->get();
            }
        }
    }
}