<?php

namespace Alvin0\RedisModel\Traits;

use LogicException;
use Alvin0\RedisModel\{
    Builder as RedisBuilder,
    Collection as RedisCollection,
    Model as RedisModel
};
use Alvin0\RedisModel\Relations\{
    BelongsTo as RedisBelongsTo,
    BelongsToMany as RedisBelongsToMany,
    HasMany as RedisHasMany,
    HasManyThrough as RedisHasManyThrough,
    HasOne as RedisHasOne,
    HasOneThrough as RedisHasOneThrough,
    // MorphMany as RedisMorphMany,
    // MorphOne as RedisMorphOne,
    // MorphTo as RedisMorphTo,
    // MorphToMany as RedisMorphToMany,
    // Pivot as RedisPivot,
    Relation as RedisRelation
};
use Illuminate\Database\Eloquent\{
    Builder as EloquentBuilder,
    Collection as EloquentCollection,
    Model as EloquentModel
};
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo as EloquentBelongsTo,
    BelongsToMany as EloquentBelongsToMany,
    HasMany as EloquentHasMany,
    HasManyThrough as EloquentHasManyThrough,
    HasOne as EloquentHasOne,
    HasOneThrough as EloquentHasOneThrough,
    MorphMany as EloquentMorphMany,
    MorphOne as EloquentMorphOne,
    MorphTo as EloquentMorphTo,
    MorphToMany as EloquentMorphToMany,
    Pivot as EloquentPivot,
    Relation as EloquentRelation
};

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
     * Instantiate a new HasOneThrough relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model|\Alvin0\RedisModel\Model
     * @template TIntermediateModel of \Illuminate\Database\Eloquent\Model|\Alvin0\RedisModel\Model
     * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model|\Alvin0\RedisModel\Model
     *
     * @param  EloquentBuilder|RedisBuilder<TRelatedModel>  $query
     * @param  TDeclaringModel  $farParent
     * @param  TIntermediateModel  $throughParent
     * @param  string  $firstKey
     * @param  string  $secondKey
     * @param  string  $localKey
     * @param  string  $secondLocalKey
     * @return EloquentHasOneThrough|RedisHasOneThrough<TRelatedModel, TIntermediateModel, TDeclaringModel>
     */
    protected function newHasOneThrough(EloquentBuilder | RedisBuilder $query, EloquentModel | RedisModel $farParent, EloquentModel | RedisModel $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey): EloquentHasOneThrough | RedisHasOneThrough
    {
        if ($query instanceof RedisBuilder || $farParent instanceof RedisModel || $throughParent instanceof RedisModel) {
            return new RedisHasOneThrough($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
        }

        return new EloquentHasOneThrough($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
    }

    /**
     * Instantiate a new HasManyThrough relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     * @template TIntermediateModel of \Illuminate\Database\Eloquent\Model
     * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  \Illuminate\Database\Eloquent\Builder<TRelatedModel>  $query
     * @param  TDeclaringModel  $farParent
     * @param  TIntermediateModel  $throughParent
     * @param  string  $firstKey
     * @param  string  $secondKey
     * @param  string  $localKey
     * @param  string  $secondLocalKey
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough<TRelatedModel, TIntermediateModel, TDeclaringModel>
     */
    protected function newHasManyThrough(EloquentBuilder | RedisBuilder $query, EloquentModel | RedisModel $farParent, EloquentModel | RedisModel $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey)
    {
        if ($query instanceof RedisBuilder || $farParent instanceof RedisModel || $throughParent instanceof RedisModel) {
            return new RedisHasManyThrough($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
        }

        return new EloquentHasManyThrough($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
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

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (str_starts_with($method, 'through') && $method !== 'through') {
            $relationship = lcfirst(substr($method, 7));
            return $this->through($relationship);
        }

        if (str_starts_with($method, 'has')) {
            $relationship = lcfirst(substr($method, 3));
            return $this->$relationship();
        }

        return parent::__call($method, $parameters);
    }

    /**
     * Begin a fluent has-one-through relationship chain.
     *
     * @param  string  $relationship
     * @return \Alvin0\RedisModel\PendingHasThroughRelationship
     */
    public function through($relationship)
    {
        $relationship = lcfirst($relationship);
        return new \Alvin0\RedisModel\PendingHasThroughRelationship($this, $this->$relationship());
    }

    /**
     * Define the target relationship in a has-through chain.
     *
     * @param  string  $relationship
     * @return mixed
     */
    public function has($relationship)
    {
        $relationship = lcfirst($relationship);
        return $this->$relationship();
    }
}