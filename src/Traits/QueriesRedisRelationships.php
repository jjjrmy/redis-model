<?php

namespace Alvin0\RedisModel\Traits;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Str;
use InvalidArgumentException;
use BadMethodCallException;
use Alvin0\RedisModel\Model as RedisModel;
use Alvin0\RedisModel\Collection as RedisCollection;

trait QueriesRedisRelationships
{
    /**
     * Add a "belongs to" relationship where clause to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Alvin0\RedisModel\Model  $related
     * @param  string|null  $relationshipName
     * @param  string  $boolean
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\RelationNotFoundException
     */
    public function whereBelongsTo($related, $relationshipName = null, $boolean = 'and')
    {
        if (! $related instanceof EloquentCollection && ! $related instanceof RedisCollection) {
            $relatedCollection = $related->newCollection([$related]);
        } else {
            $relatedCollection = $related;
            $related = $relatedCollection->first();
        }

        if ($relatedCollection->isEmpty()) {
            throw new InvalidArgumentException('Collection given to whereBelongsTo method may not be empty.');
        }

        if ($relationshipName === null) {
            $relationshipName = Str::camel(class_basename($related));
        }

        try {
            $relationship = $this->model->{$relationshipName}();
        } catch (BadMethodCallException) {
            throw RelationNotFoundException::make($this->model, $relationshipName);
        }

        if (! $relationship instanceof BelongsTo) {
            throw RelationNotFoundException::make($this->model, $relationshipName, BelongsTo::class);
        }

        $this->whereIn(
            $relationship->getQualifiedForeignKeyName(),
            $relatedCollection->pluck($relationship->getOwnerKeyName())->toArray(),
            $boolean,
        );

        return $this;
    }
} 