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
     * Instantiate a new HasMany relationship.
     *
     * @template TRelatedModel of EloquentModel|RedisModel
     * @template TDeclaringModel of EloquentModel|RedisModel
     *
     * @param  EloquentBuilder|RedisBuilder<TRelatedModel>  $query
     * @param  TDeclaringModel  $parent
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return EloquentHasMany|RedisHasMany<TRelatedModel, TDeclaringModel>
     */
    protected function newHasMany(EloquentBuilder | RedisBuilder $query, EloquentModel | RedisModel $parent, $foreignKey, $localKey)
    {
        if ($query instanceof RedisBuilder || $parent instanceof RedisModel) {
            return new RedisHasMany($query, $parent, $foreignKey, $localKey);
        }

        return new EloquentHasMany($query, $parent, $foreignKey, $localKey);
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
     * Instantiate a new BelongsToMany relationship.
     *
     * @template TRelatedModel of EloquentModel|RedisModel
     * @template TDeclaringModel of EloquentModel|RedisModel
     *
     * @param  EloquentBuilder|RedisBuilder<TRelatedModel>  $query
     * @param  TDeclaringModel  $parent
     * @param  string|class-string<\Illuminate\Database\Eloquent\Model>  $table
     * @param  string  $foreignPivotKey
     * @param  string  $relatedPivotKey
     * @param  string  $parentKey
     * @param  string  $relatedKey
     * @param  string|null  $relationName
     * @return EloquentBelongsToMany|RedisBelongsToMany<TRelatedModel, TDeclaringModel>
     */
    protected function newBelongsToMany(EloquentBuilder | RedisBuilder $query, EloquentModel | RedisModel $parent, $table, $foreignPivotKey, $relatedPivotKey,
                                        $parentKey, $relatedKey, $relationName = null)
    {
        if ($query instanceof RedisBuilder || $parent instanceof RedisModel) {
            return new RedisBelongsToMany($query, $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName);
        }

        return new EloquentBelongsToMany($query, $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName);
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
        return tap(new $class, function ($instance) {
            if ($this instanceof RedisModel || $instance instanceof RedisModel) return;
            if (! $instance->getConnectionName()) {
                $instance->setConnection($this->connection);
            }
        });
    }

    public function withDefault($callback = true)
    {
        $this->withDefault = true;
        $this->defaultCallback = $callback;

        return $this;
    }

    protected function getDefaultFor($parent)
    {
        if (! $this->withDefault) {
            return;
        }

        $instance = $this->related->newInstance();
        $instance->exists = false;
        $instance->id = 0;

        if (is_callable($this->defaultCallback)) {
            call_user_func($this->defaultCallback, $instance);
        } elseif (is_array($this->defaultCallback)) {
            $instance->forceFill($this->defaultCallback);
        }

        return $instance;
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Alvin0\RedisModel\EloquentBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new \Alvin0\RedisModel\EloquentBuilder($query);
    }
}