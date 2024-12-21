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
 * @extends \Illuminate\Database\Eloquent\Relations\BelongsTo<TRelatedModel, TDeclaringModel>
 */
class BelongsTo extends \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    /**
     * Create a new belongs to relationship instance.
     *
     * @param  Builder|RedisBuilder  $query
     * @param  TDeclaringModel  $child
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @param  string|null  $relationName
     * @return void
     */
    public function __construct(Builder|RedisBuilder $query, EloquentModel|RedisModel $child, string $foreignKey, string $ownerKey, ?string $relationName = null)
    {
        $this->ownerKey = $ownerKey;
        $this->foreignKey = $foreignKey;
        $this->relationName = $relationName;
        $this->child = $child;

        // Initialize the relation without calling parent constructor
        $this->query = $query;
        $this->related = $query->getModel();

        $this->addConstraints();
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

        return parent::getResults();
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
            
            $keys = collect([$this->child])->map(function ($model) {
                return $model->{$this->foreignKey};
            })->filter()->values()->all();
            
            foreach ($keys as $key) {
                if ($record = $this->related::where($this->ownerKey, $key)->first()) {
                    $results->push($record);
                }
            }

            return $results;
        }

        return parent::getEager();
    }

    /**
     * Get the value of the model's foreign key.
     *
     * @param  EloquentModel|RedisModel  $model
     * @return mixed
     */
    protected function getForeignKeyFrom(EloquentModel|RedisModel $model)
    {
        return $model->{$this->foreignKey};
    }
} 