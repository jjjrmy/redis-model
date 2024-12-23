<?php

namespace Alvin0\RedisModel\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Alvin0\RedisModel\Collection as RedisCollection;
use Illuminate\Database\Eloquent\Builder;
use Alvin0\RedisModel\Builder as RedisBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Alvin0\RedisModel\Relations\Concerns\InteractsWithPivot;

/**
 * @template TRelatedModel of EloquentModel|RedisModel
 * @template TDeclaringModel of EloquentModel|RedisModel
 * @extends \Illuminate\Database\Eloquent\Relations\BelongsToMany<TRelatedModel, TDeclaringModel>
 */
class BelongsToMany extends \Illuminate\Database\Eloquent\Relations\BelongsToMany
{
    use InteractsWithPivot;

    /**
     * Create a new belongs to many relationship instance.
     *
     * @param  Builder|RedisBuilder  $query
     * @param  TDeclaringModel  $parent
     * @param  string  $table
     * @param  string  $foreignPivotKey
     * @param  string  $relatedPivotKey
     * @param  string  $parentKey
     * @param  string  $relatedKey
     * @param  string|null  $relationName
     * @return void
     */
    public function __construct(Builder|RedisBuilder $query, EloquentModel|RedisModel $parent, string $table, string $foreignPivotKey, string $relatedPivotKey, string $parentKey, string $relatedKey, ?string $relationName = null)
    {
        $this->table = $table;
        $this->parentKey = $parentKey;
        $this->relatedKey = $relatedKey;
        $this->relationName = $relationName;
        $this->foreignPivotKey = $foreignPivotKey;
        $this->relatedPivotKey = $relatedPivotKey;

        // Initialize the relation without calling parent constructor
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
    public function addConstraints()
    {
        if (static::$constraints) {
            if ($this->parent instanceof RedisModel || $this->related instanceof RedisModel) {
                return;
            }

            $this->performJoin();

            $this->query->where(
                $this->getQualifiedForeignPivotKeyName(),
                '=',
                $this->parent->{$this->parentKey}
            );
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array<int, TDeclaringModel>  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        if ($this->parent instanceof RedisModel || $this->related instanceof RedisModel) {
            return;
        }

        $this->query->whereIn(
            $this->getQualifiedForeignPivotKeyName(),
            collect($models)->map(function ($model) {
                return $model->{$this->parentKey};
            })->all()
        );
    }

    /**
     * Get the results of the relationship.
     *
     * @return Collection|RedisCollection
     */
    public function getResults()
    {
        if ($this->parent instanceof RedisModel || $this->related instanceof RedisModel) {
            $parentKey = $this->parent->{$this->parentKey};
            $relatedIds = $this->getPivotIds($parentKey);
            
            if (empty($relatedIds)) {
                return $this->related instanceof RedisModel 
                    ? new RedisCollection 
                    : new Collection;
            }

            $results = collect();
            foreach ($relatedIds as $id) {
                if ($model = $this->related::find($id)) {
                    $pivotData = $this->getPivotData($parentKey, $id);
                    $pivot = $this->newExistingPivot($pivotData);
                    
                    $model->setRelation($this->accessor, $pivot);
                    $results->push($model);
                }
            }
            
            return $results;
        }

        return parent::getResults();
    }

    /**
     * Get the related IDs from the pivot table.
     *
     * @param  mixed  $parentKey
     * @return array
     */
    protected function getPivotIds($parentKey)
    {
        return DB::table($this->table)
            ->where($this->foreignPivotKey, $parentKey)
            ->pluck($this->relatedPivotKey)
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Get the pivot data for a specific relationship.
     *
     * @param  mixed  $parentKey
     * @param  mixed  $relatedKey
     * @return array
     */
    protected function getPivotData($parentKey, $relatedKey)
    {
        $record = DB::table($this->table)
            ->where($this->foreignPivotKey, $parentKey)
            ->where($this->relatedPivotKey, $relatedKey)
            ->first();

        if (!$record) {
            return [];
        }

        $attributes = (array) $record;
        $pivotColumns = array_merge(
            [$this->foreignPivotKey, $this->relatedPivotKey],
            $this->pivotColumns
        );

        return array_intersect_key($attributes, array_flip($pivotColumns));
    }

    /**
     * Create a new pivot model instance.
     *
     * @param  array  $attributes
     * @param  bool  $exists
     * @return \Illuminate\Database\Eloquent\Relations\Pivot
     */
    public function newPivot(array $attributes = [], $exists = false)
    {
        if ($this->using) {
            return $this->newPivotInstance($attributes, $exists);
        }

        // If either parent or related is a Redis model, we need to handle it differently
        if ($this->parent instanceof RedisModel || $this->related instanceof RedisModel) {
            $pivot = new Pivot;
            
            $pivot->setTable($this->table)
                ->forceFill($attributes)
                ->syncOriginal();

            $pivot->exists = $exists;

            return $pivot->setPivotKeys($this->foreignPivotKey, $this->relatedPivotKey);
        }

        return parent::newPivot($attributes, $exists);
    }

    /**
     * Create a new pivot model instance from an existing pivot model instance.
     *
     * @param  array  $attributes
     * @param  bool  $exists
     * @return \Illuminate\Database\Eloquent\Relations\Pivot
     */
    protected function newPivotInstance(array $attributes = [], $exists = false)
    {
        $class = $this->using;

        if ($this->parent instanceof RedisModel || $this->related instanceof RedisModel) {
            $pivot = new $class;
            
            $pivot->setTable($this->table)
                ->forceFill($attributes)
                ->syncOriginal();

            $pivot->exists = $exists;

            return $pivot->setPivotKeys($this->foreignPivotKey, $this->relatedPivotKey);
        }

        return parent::newPivotInstance($attributes, $exists);
    }
} 