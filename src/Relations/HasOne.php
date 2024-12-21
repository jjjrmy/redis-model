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
 * @extends \Illuminate\Database\Eloquent\Relations\HasOne<TRelatedModel, TDeclaringModel>
 */
class HasOne extends \Illuminate\Database\Eloquent\Relations\HasOne
{
    /**
     * Create a new has one relationship instance.
     *
     * @param  Builder|RedisBuilder  $query
     * @param  TDeclaringModel  $parent
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return void
     */
    public function __construct(Builder|RedisBuilder $query, EloquentModel|RedisModel $parent, string $foreignKey, string $localKey)
    {
        $this->localKey = $localKey;
        $this->foreignKey = $foreignKey;

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
            if (!($this->related instanceof RedisModel)) {
                $this->query->where($this->foreignKey, $this->getParentKey());
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
        $keys = collect($models)->map(function ($model) {
            return $model->{$this->localKey};
        })->all();

        if (!($this->related instanceof RedisModel)) {
            $this->query->whereIn($this->foreignKey, $keys);
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

        return parent::getResults();
    }

    /**
     * Get the results of the relationship as a collection.
     *
     * @param  array  $columns
     * @return Collection|RedisCollection
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
     * @return Collection|RedisCollection
     */
    public function getEager()
    {
        if ($this->related instanceof RedisModel) {
            $results = new RedisCollection();
            $records = $this->related::all();
            
            foreach ($records as $record) {
                if (in_array($record->{$this->getForeignKeyName()}, $this->getEagerModelKeys($this->parent))) {
                    $results->push($record);
                }
            }

            return $results;
        }

        return parent::getEager();
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