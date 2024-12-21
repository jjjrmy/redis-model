<?php

namespace Alvin0\RedisModel\Traits;

use Alvin0\RedisModel\Builder as RedisBuilder;
use Alvin0\RedisModel\Collection as RedisCollection;
use Alvin0\RedisModel\Model as RedisModel;
use Alvin0\RedisModel\Relations\BelongsTo as RedisBelongsTo;
use Alvin0\RedisModel\Relations\BelongsToMany as RedisBelongsToMany;
use Alvin0\RedisModel\Relations\HasMany as RedisHasMany;
use Alvin0\RedisModel\Relations\HasManyThrough as RedisHasManyThrough;
use Alvin0\RedisModel\Relations\HasOne as RedisHasOne;
use Alvin0\RedisModel\Relations\HasOneThrough as RedisHasOneThrough;
// use Alvin0\RedisModel\Relations\MorphMany as RedisMorphMany;
// use Alvin0\RedisModel\Relations\MorphOne as RedisMorphOne;
// use Alvin0\RedisModel\Relations\MorphTo as RedisMorphTo;
// use Alvin0\RedisModel\Relations\MorphToMany as RedisMorphToMany;
// use Alvin0\RedisModel\Relations\Pivot as RedisPivot;
use Alvin0\RedisModel\Relations\Relation as RedisRelation;
use Closure;
use Illuminate\Database\ClassMorphViolationException;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\PendingHasThroughRelationship;
use Illuminate\Database\Eloquent\Relations\BelongsTo as EloquentBelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as EloquentBelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough as EloquentHasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne as EloquentHasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough as EloquentHasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany as EloquentMorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne as EloquentMorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo as EloquentMorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany as EloquentMorphToMany;
use Illuminate\Database\Eloquent\Relations\Pivot as EloquentPivot;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LogicException;

trait HasRedisRelationships
{
    /**
     * Define a one-to-one relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TRelatedModel>  $related
     * @param  string|null  $foreignKey
     * @param  string|null  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<TRelatedModel, $this>
     */
    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return $this->newHasOne($instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey);
    }

    /**
     * Instantiate a new HasOne relationship.
     *
     * @template TRelatedModel of EloquentModel|RedisModel
     * @template TDeclaringModel of EloquentModel|RedisModel
     *
     * @param  EloquentBuilder|RedisBuilder<TRelatedModel>  $query
     * @param  TDeclaringModel  $parent
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return EloquentHasOne|RedisHasOne<TRelatedModel, TDeclaringModel>
     */
    protected function newHasOne(EloquentBuilder | RedisBuilder $query, EloquentModel | RedisModel $parent, $foreignKey, $localKey): EloquentHasOne | RedisHasOne
    {
        if ($query instanceof RedisBuilder || $parent instanceof RedisModel) {
            return new RedisHasOne($query, $parent, $foreignKey, $localKey);
        }

        return new EloquentHasOne($query, $parent, $foreignKey, $localKey);
    }

    /**
     * Instantiate a new BelongsTo relationship.
     *
     * @template TRelatedModel of EloquentModel|RedisModel
     * @template TDeclaringModel of EloquentModel|RedisModel
     *
     * @param  EloquentBuilder|RedisBuilder<TRelatedModel>  $query
     * @param  TDeclaringModel  $child
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @param  string  $relation
     * @return EloquentBelongsTo|RedisBelongsTo<TRelatedModel, TDeclaringModel>
     */
    protected function newBelongsTo(EloquentBuilder | RedisBuilder $query, EloquentModel | RedisModel $child, $foreignKey, $ownerKey, $relation): EloquentBelongsTo | RedisBelongsTo
    {
        if ($query instanceof RedisBuilder || $child instanceof RedisModel) {
            return new RedisBelongsTo($query, $child, $foreignKey, $ownerKey, $relation);
        }

        return new EloquentBelongsTo($query, $child, $foreignKey, $ownerKey, $relation);
    }

    /**
     * Get a relationship value from a method.
     *
     * @param  string  $method
     * @return mixed
     *
     * @throws \LogicException
     */
    public function getRelationshipFromMethod($method)
    {
        $relation = $this->$method();

        if (!($relation instanceof RedisRelation || $relation instanceof EloquentRelation)) {
            if (is_null($relation)) {
                throw new LogicException(sprintf(
                    '%s::%s must return a relationship instance, but "null" was returned. Was the "return" keyword used?', static::class, $method
                ));
            }

            throw new LogicException(sprintf(
                '%s::%s must return a relationship instance.', static::class, $method
            ));
        }

        return tap($relation->getResults(), function ($results) use ($method) {
            $this->setRelation($method, $results);
        });
    }

    protected function newRelatedInstance($class)
    {
        return new $class;
    }
}