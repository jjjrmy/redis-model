<?php

namespace Alvin0\RedisModel\Relations\Concerns;

use Alvin0\RedisModel\Builder;

trait InteractsWithPivot
{
    /**
     * Create a new pivot statement for inserting records.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function newPivotStatement()
    {
        // Always use SQL query builder for pivot operations
        return $this->query->getQuery()->newQuery()->from($this->table);
    }

    /**
     * Create a new query builder for the pivot table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function newPivotQuery()
    {
        $query = $this->newPivotStatement();

        foreach ($this->pivotWheres as $arguments) {
            $query->where(...$arguments);
        }

        foreach ($this->pivotWhereIns as $arguments) {
            $query->whereIn(...$arguments);
        }

        foreach ($this->pivotWhereNulls as $arguments) {
            $query->whereNull(...$arguments);
        }

        return $query->where($this->getQualifiedForeignPivotKeyName(), $this->parent->{$this->parentKey});
    }

    /**
     * Get all of the IDs from the given mixed value.
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
}
