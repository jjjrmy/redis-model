<?php

namespace Alvin0\RedisModel\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Alvin0\RedisModel\Collection as RedisCollection;
use Illuminate\Database\Eloquent\Builder;
use Alvin0\RedisModel\Builder as RedisBuilder;

/**
 * @template TRelatedModel of EloquentModel|RedisModel
 * @template TDeclaringModel of EloquentModel|RedisModel
 * @extends \Illuminate\Database\Eloquent\Relations\HasMany<TRelatedModel, TDeclaringModel>
 */
class HasMany extends \Illuminate\Database\Eloquent\Relations\HasMany
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
     * @param  Builder|RedisBuilder  $query
     * @param  TDeclaringModel  $parent
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return void
     */
    public function __construct(Builder|RedisBuilder $query, EloquentModel|RedisModel $parent, string $foreignKey, string $localKey)
    {
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
        
        // Initialize the relation without calling parent constructor
        $this->query = $query;
        $this->parent = $parent;
        $this->related = $query->getModel();

        $this->addConstraints();
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
     * @param  array<int, TDeclaringModel>  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $this->eagerKeys = collect($models)->map(function ($model) {
            return $model->{$this->localKey};
        })->all();
    }

    /**
     * Get the results of the relationship.
     *
     * @return Collection|RedisCollection
     */
    public function getResults()
    {
        $parentKey = $this->getParentKey();
        
        if (is_null($parentKey)) {
            return $this->related->newCollection();
        }

        if ($this->parent instanceof EloquentModel && $this->related instanceof RedisModel) {
            return $this->related::where($this->foreignKey, $parentKey)->get();
        }

        if ($this->related instanceof RedisModel) {
            return $this->related::where($this->foreignKey, $parentKey)->get();
        }

        return parent::getResults();
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
     * @param  \Illuminate\Database\Eloquent\Collection|\Alvin0\RedisModel\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            $key = $model->{$this->localKey};
            
            if (isset($dictionary[(string)$key])) {
                $collection = $this->related instanceof RedisModel 
                    ? new RedisCollection($dictionary[(string)$key])
                    : new Collection($dictionary[(string)$key]);
                    
                $model->setRelation($relation, $collection);
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
            $foreignKey = str_replace($this->related->getTable().'.', '', $this->foreignKey);
            $dictionary[(string)$result->{$foreignKey}][] = $result;
        }

        return $dictionary;
    }

    /**
     * Get the results of the relationship as a collection.
     *
     * @param  array  $columns
     * @return Collection|RedisCollection
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
     * @return Collection|RedisCollection
     */
    public function getEager()
    {
        $results = $this->related instanceof RedisModel ? new RedisCollection() : new Collection();
            
        foreach ($this->getEagerModelKeys() as $key) {
            if ($this->parent instanceof EloquentModel && $this->related instanceof RedisModel) {
                $records = $this->related::where($this->foreignKey, (string)$key)->get();
                foreach ($records as $record) {
                    $results->push($record);
                }
            } else if ($this->related instanceof RedisModel) {
                $records = $this->related::where($this->foreignKey, (string)$key)->get();
                foreach ($records as $record) {
                    $results->push($record);
                }
            } else {
                $records = $this->query->where($this->foreignKey, '=', $key)->get();
                foreach ($records as $record) {
                    $results->push($record);
                }
            }
        }

        return $results;
    }

    /**
     * Get the keys for eager loading.
     *
     * @return array
     */
    protected function getEagerModelKeys()
    {
        return collect($this->eagerKeys)->unique()->values()->all();
    }

    /**
     * Create a new instance of the related model.
     *
     * @param  array  $attributes
     * @return EloquentModel|RedisModel
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