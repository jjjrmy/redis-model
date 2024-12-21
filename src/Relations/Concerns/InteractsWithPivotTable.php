<?php

namespace Alvin0\RedisModel\Relations\Concerns;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Alvin0\RedisModel\Collection as RedisCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait InteractsWithPivotTable
{
    /**
     * Attach a model to the parent.
     *
     * @param  mixed  $id
     * @param  array  $attributes
     * @param  bool  $touch
     * @return void
     */
    public function attach($id, array $attributes = [], $touch = true)
    {
        $this->newPivotStatement()->insert($this->formatAttachRecords(
            $this->parseIds($id), $attributes
        ));

        if ($touch) {
            $this->touchIfTouching();
        }
    }

    /**
     * Create a new query builder for the pivot table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newPivotStatement()
    {
        return DB::table($this->table);
    }

    /**
     * Create an array of records to insert into the pivot table.
     *
     * @param  array  $ids
     * @param  array  $attributes
     * @return array
     */
    protected function formatAttachRecords($ids, array $attributes)
    {
        $records = [];

        $hasTimestamps = ($this->hasPivotColumn($this->createdAt()) ||
                  $this->hasPivotColumn($this->updatedAt()));

        foreach ($ids as $id) {
            $records[] = $this->formatAttachRecord(
                $id, $attributes, $hasTimestamps
            );
        }

        return $records;
    }

    /**
     * Create a full attachment record payload.
     *
     * @param  mixed  $id
     * @param  array  $attributes
     * @param  bool  $hasTimestamps
     * @return array
     */
    protected function formatAttachRecord($id, array $attributes, $hasTimestamps)
    {
        return array_merge(
            $this->baseAttachRecord($id, $hasTimestamps),
            $attributes
        );
    }

    /**
     * Create a new base attach record.
     *
     * @param  mixed  $id
     * @param  bool  $hasTimestamps
     * @return array
     */
    protected function baseAttachRecord($id, $hasTimestamps)
    {
        $record[$this->foreignPivotKey] = $this->parent->{$this->parentKey};
        $record[$this->relatedPivotKey] = $id;

        if ($hasTimestamps) {
            $record[$this->createdAt()] = now();
            $record[$this->updatedAt()] = now();
        }

        return $record;
    }

    /**
     * Get all of the IDs from the given mixed value.
     *
     * @param  mixed  $value
     * @return array
     */
    protected function parseIds($value)
    {
        if ($value instanceof RedisModel || $value instanceof EloquentModel) {
            return [$value->{$this->relatedKey}];
        }

        if ($value instanceof Collection || $value instanceof RedisCollection) {
            return $value->pluck($this->relatedKey)->all();
        }

        if ($value instanceof BaseCollection) {
            return $value->map(function ($item) {
                if ($item instanceof RedisModel || $item instanceof EloquentModel) {
                    return $item->{$this->relatedKey};
                }
                return $item;
            })->all();
        }

        return (array) $value;
    }

    /**
     * Touch all of the related models for the relationship.
     *
     * @return void
     */
    public function touchIfTouching()
    {
        if ($this->touchingParent()) {
            $this->parent->touch();
        }

        if ($this->getParent()->touches($this->relationName)) {
            $this->touch();
        }
    }

    /**
     * Determine if we should touch the parent on sync.
     *
     * @return bool
     */
    protected function touchingParent()
    {
        return $this->getRelated()->touches($this->guessInverseRelation());
    }

    /**
     * Attempt to guess the name of the inverse of the relation.
     *
     * @return string
     */
    protected function guessInverseRelation()
    {
        return Str::camel(Str::pluralStudly(class_basename($this->getParent())));
    }

    /**
     * Get the name of the "created at" column.
     *
     * @return string
     */
    public function createdAt()
    {
        return $this->parent->getCreatedAtColumn();
    }

    /**
     * Get the name of the "updated at" column.
     *
     * @return string
     */
    public function updatedAt()
    {
        return $this->parent->getUpdatedAtColumn();
    }

    /**
     * Determine whether a pivot column exists.
     *
     * @param  string  $column
     * @return bool
     */
    protected function hasPivotColumn($column)
    {
        return in_array($column, $this->getPivotColumns());
    }

    /**
     * Get the pivot columns for the relation.
     *
     * @return array
     */
    protected function getPivotColumns()
    {
        return [$this->foreignPivotKey, $this->relatedPivotKey];
    }
} 