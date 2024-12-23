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
        if (!$related instanceof EloquentCollection) {
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

        if (!$relationship instanceof BelongsTo) {
            throw RelationNotFoundException::make($this->model, $relationshipName, BelongsTo::class);
        }

        // For Redis models, we need to use the where method with the foreign key
        // If we have a collection, we need to handle it differently
        if ($relatedCollection->count() > 1) {
            $ownerKeys = $relatedCollection->pluck($relationship->getOwnerKeyName())->all();
            $foreignKey = $relationship->getForeignKeyName();
            
            // For Redis models, we need to handle each key individually
            $this->setConditionSession([]);
            foreach ($ownerKeys as $ownerKey) {
                $this->where($foreignKey, $ownerKey);
            }
            return $this;
        }

        // For Redis models, we need to handle the subKeys
        if ($this->model instanceof RedisModel) {
            $foreignKey = $relationship->getForeignKeyName();
            $ownerKey = $relatedCollection->pluck($relationship->getOwnerKeyName())->first();
            
            // Reset any existing conditions
            $this->setConditionSession([]);
            
            // Add the foreign key condition
            return $this->where($foreignKey, $ownerKey);
        }

        return $this->where(
            $relationship->getForeignKeyName(),
            $relatedCollection->pluck($relationship->getOwnerKeyName())->first()
        );
    }
} 