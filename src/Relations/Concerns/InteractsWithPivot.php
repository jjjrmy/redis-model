<?php

namespace Alvin0\RedisModel\Relations\Concerns;

use Alvin0\RedisModel\Builder;

trait InteractsWithPivot
{
    /**
     * The foreign key of the parent model.
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * The associated key of the relation.
     *
     * @var string
     */
    protected $relatedKey;

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
        if ($this->using) {
            $this->attachUsingCustomClass($id, $attributes);
        } else {
            // Always use SQL for pivot operations
            $this->newPivotStatement()->insert(
                $this->formatAttachRecords($this->parseIds($id), $attributes)
            );
        }

        if ($touch) {
            $this->touchIfTouching();
        }
    }

    /**
     * Create a new pivot statement for inserting records.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function newPivotStatement()
    {
        return $this->query->getQuery()->newQuery()->from($this->table);
    }

    /**
     * Format the sync / attach records.
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
            $records[] = $this->formatAttachRecord($id, $id, $attributes, $hasTimestamps);
        }

        return $records;
    }

    /**
     * Format a single attach record.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @param  bool   $hasTimestamps
     * @return array
     */
    protected function formatAttachRecord($key, $value, $attributes, $hasTimestamps)
    {
        list($id, $attributes) = $this->extractAttachIdAndAttributes($key, $value, $attributes);

        return array_merge(
            $this->baseAttachRecord($id, $hasTimestamps),
            $attributes
        );
    }

    /**
     * Get the base attach record.
     *
     * @param  mixed  $id
     * @param  bool   $hasTimestamps
     * @return array
     */
    protected function baseAttachRecord($id, $hasTimestamps)
    {
        $record = [
            $this->getRelatedPivotKeyName() => $id,
            $this->getForeignPivotKeyName() => $this->parent->getKey(),
        ];

        if ($hasTimestamps) {
            $record[$this->createdAt()] = $this->parent->freshTimestamp();
            $record[$this->updatedAt()] = $this->parent->freshTimestamp();
        }

        return $record;
    }

    /**
     * Get the foreign key for the relation.
     *
     * @return string
     */
    public function getForeignKeyName()
    {
        return $this->foreignKey;
    }

    /**
     * Get the related key for the relation.
     *
     * @return string
     */
    public function getRelatedKeyName()
    {
        return $this->relatedKey;
    }

    /**
     * Get the foreign pivot key name.
     *
     * @return string
     */
    public function getForeignPivotKeyName()
    {
        return $this->foreignPivotKey;
    }

    /**
     * Get the related pivot key name.
     *
     * @return string
     */
    public function getRelatedPivotKeyName()
    {
        return $this->relatedPivotKey;
    }

    /**
     * Parse the IDs into an array.
     *
     * @param  mixed  $value
     * @return array
     */
    protected function parseIds($value)
    {
        if ($value instanceof \Illuminate\Database\Eloquent\Model) {
            return [$value->getKey()];
        }

        if ($value instanceof \Illuminate\Support\Collection) {
            return $value->modelKeys();
        }

        if ($value instanceof \Illuminate\Database\Eloquent\Collection) {
            return $value->modelKeys();
        }

        return (array) $value;
    }

    /**
     * Extract the id and extra attributes from the attach record.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     */
    protected function extractAttachIdAndAttributes($key, $value, array $attributes)
    {
        return [$value, $attributes];
    }
}
