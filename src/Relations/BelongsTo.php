<?php

namespace Alvin0\RedisModel\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Alvin0\RedisModel\Collection as RedisCollection;
use Illuminate\Database\Eloquent\Builder;
use Alvin0\RedisModel\Builder as RedisBuilder;
use Alvin0\RedisModel\Relations\Concerns\SupportsDefaultModels;

/**
 * @template TRelatedModel of EloquentModel|RedisModel
 * @template TDeclaringModel of EloquentModel|RedisModel
 * @extends \Illuminate\Database\Eloquent\Relations\BelongsTo<TRelatedModel, TDeclaringModel>
 */
class BelongsTo extends \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    use SupportsDefaultModels;

    /**
     * The keys for eager loading.
     *
     * @var array
     */
    protected $eagerKeys = [];

    /**
     * Create a new belongs to relationship instance.
     *
     * @param  Builder|RedisBuilder  $query
     * @param  TDeclaringModel  $child
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @param  string  $relation
     * @return void
     */
    public function __construct(Builder|RedisBuilder $query, EloquentModel|RedisModel $child, string $foreignKey, string $ownerKey, string $relation)
    {
        $this->ownerKey = $ownerKey;
        $this->foreignKey = $foreignKey;
        $this->relationName = $relation;
        $this->child = $child;

        // Initialize the relation without calling parent constructor
        $this->query = $query;
        $this->related = $query->getModel();

        $this->addConstraints();
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
            return $model->{$this->foreignKey};
        })->filter()->values()->all();
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
        $foreign = $this->foreignKey;
        $owner = $this->ownerKey;

        $dictionary = [];
        foreach ($results as $result) {
            $dictionary[(string)$result->{$owner}] = $result;
        }

        foreach ($models as $model) {
            $key = $model->{$foreign};
            
            if (isset($dictionary[(string)$key])) {
                $model->setRelation($relation, $dictionary[(string)$key]);
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
        if (is_null($this->child->{$this->foreignKey})) {
            return $this->getDefaultFor($this->child);
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
        $results = $this->related instanceof RedisModel ? new RedisCollection() : new Collection();
            
        foreach ($this->eagerKeys as $key) {
            if ($this->related instanceof RedisModel) {
                if ($record = $this->related::where($this->ownerKey, (string)$key)->first()) {
                    $results->push($record);
                }
            } else {
                if ($record = $this->query->where($this->ownerKey, '=', $key)->first()) {
                    $results->push($record);
                }
            }
        }

        return $results;
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

    /**
     * Make a new related instance for the given model.
     *
     * @param  EloquentModel|RedisModel  $parent
     * @return EloquentModel|RedisModel
     */
    protected function newRelatedInstanceFor(EloquentModel|RedisModel $parent)
    {
        return $this->related->newInstance();
    }
} 