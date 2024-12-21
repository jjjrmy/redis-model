<?php

namespace Alvin0\RedisModel\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Alvin0\RedisModel\Builder as RedisBuilder;

/**
 * @template TRelatedModel of EloquentModel|RedisModel
 * @template TDeclaringModel of EloquentModel|RedisModel
 * @template TResult
 *
 * @extends \Illuminate\Database\Eloquent\Relations\Relation<TRelatedModel, TDeclaringModel, TResult>
 */
abstract class Relation extends \Illuminate\Database\Eloquent\Relations\Relation
{
    /**
     * The query builder instance.
     *
     * @var EloquentBuilder|RedisBuilder
     */
    protected $query;

    /**
     * Create a new relationship instance.
     *
     * @param  EloquentBuilder|RedisBuilder  $query
     * @param  TDeclaringModel  $parent
     * @return void
     */
    public function __construct(EloquentBuilder|RedisBuilder $query, EloquentModel|RedisModel $parent)
    {
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
    abstract public function addConstraints();

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array<int, TDeclaringModel>  $models
     * @return void
     */
    abstract public function addEagerConstraints(array $models);

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array<int, TDeclaringModel>  $models
     * @param  string  $relation
     * @return array<int, TDeclaringModel>
     */
    abstract public function initRelation(array $models, $relation);

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array<int, TDeclaringModel>  $models
     * @param  \Illuminate\Database\Eloquent\Collection<int, TRelatedModel>  $results
     * @param  string  $relation
     * @return array<int, TDeclaringModel>
     */
    abstract public function match(array $models, Collection $results, $relation);

    /**
     * Get the results of the relationship.
     *
     * @return TResult
     */
    abstract public function getResults();
}