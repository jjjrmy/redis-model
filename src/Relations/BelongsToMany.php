<?php

namespace Alvin0\RedisModel\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Alvin0\RedisModel\Collection as RedisCollection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;
use Alvin0\RedisModel\Relations\Concerns\InteractsWithPivotTable;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BelongsToMany extends Relation
{
    use InteractsWithPivotTable;

    /**
     * The intermediate table for the relation.
     *
     * @var string
     */
    protected $table;

    /**
     * The foreign key of the parent model.
     *
     * @var string
     */
    protected $foreignPivotKey;

    /**
     * The associated key of the relation.
     *
     * @var string
     */
    protected $relatedPivotKey;

    /**
     * The key name of the parent model.
     *
     * @var string
     */
    protected $parentKey;

    /**
     * The key name of the related model.
     *
     * @var string
     */
    protected $relatedKey;

    /**
     * The "name" of the relationship.
     *
     * @var string
     */
    protected $relationName;

    /**
     * The keys for eager loading.
     *
     * @var array
     */
    protected $eagerKeys = [];

    /**
     * The pivot table columns to retrieve.
     *
     * @var array
     */
    protected $pivotColumns = [];

    /**
     * Indicates if timestamps are available on the pivot table.
     *
     * @var bool
     */
    public $withTimestamps = false;

    /**
     * The custom pivot table column for the created_at timestamp.
     *
     * @var string|null
     */
    protected $pivotCreatedAt;

    /**
     * The custom pivot table column for the updated_at timestamp.
     *
     * @var string|null
     */
    protected $pivotUpdatedAt;

    /**
     * The class name of the custom pivot model to use for the relationship.
     *
     * @var string
     */
    protected $using;

    /**
     * The name of the accessor to use for the "pivot" relationship.
     *
     * @var string
     */
    protected $accessor = 'pivot';

    /**
     * Create a new belongs to many relationship instance.
     *
     * @param  mixed  $query
     * @param  \Illuminate\Database\Eloquent\Model|\Alvin0\RedisModel\Model  $parent
     * @param  string  $table
     * @param  string  $foreignPivotKey
     * @param  string  $relatedPivotKey
     * @param  string  $parentKey
     * @param  string  $relatedKey
     * @param  string|null  $relationName
     * @return void
     */
    public function __construct($query, $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName = null)
    {
        $this->table = $table;
        $this->parentKey = $parentKey;
        $this->relatedKey = $relatedKey;
        $this->relationName = $relationName;
        $this->foreignPivotKey = $foreignPivotKey;
        $this->relatedPivotKey = $relatedPivotKey;
        $this->related = $query->getModel();

        parent::__construct($query, $parent);
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

            $this->query
                ->join($this->table, $this->getQualifiedRelatedKeyName(), '=', $this->getQualifiedRelatedPivotKeyName())
                ->where($this->getQualifiedForeignPivotKeyName(), '=', $this->parent->{$this->parentKey});
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        if ($this->parent instanceof RedisModel || $this->related instanceof RedisModel) {
            $this->eagerKeys = collect($models)->map(function ($model) {
                return $model->{$this->parentKey};
            })->all();
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
     * Initialize the relation on a set of models.
     *
     * @param  array  $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array  $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            $key = $model->{$this->parentKey};

            if (isset($dictionary[$key])) {
                $model->setRelation(
                    $relation, $this->related->newCollection($dictionary[$key])
                );
            }
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @return array
     */
    protected function buildDictionary(Collection $results)
    {
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[$result->pivot->{$this->foreignPivotKey}][] = $result;
        }

        return $dictionary;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
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

        return $this->query->get();
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
     * Execute the query and get the results.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|\Alvin0\RedisModel\Collection
     */
    public function get($columns = ['*'])
    {
        $models = $this->query->addSelect($this->shouldSelect($columns))->getModels();

        $this->hydratePivotRelation($models);

        $result = $this->related instanceof RedisModel 
            ? new RedisCollection($models) 
            : new Collection($models);

        return $result->filter()->values();
    }

    /**
     * Get the select columns for the relation query.
     *
     * @param  array  $columns
     * @return array
     */
    protected function shouldSelect(array $columns = ['*'])
    {
        if ($columns == ['*']) {
            $columns = [$this->related->getTable().'.*'];
        }

        return array_merge($columns, $this->aliasedPivotColumns());
    }

    /**
     * Get the pivot columns for the relation.
     *
     * "pivot_" is prefixed at each column for easy removal later.
     *
     * @return array
     */
    protected function aliasedPivotColumns()
    {
        $defaults = [$this->foreignPivotKey, $this->relatedPivotKey];

        return collect(array_merge($defaults, $this->pivotColumns))->map(function ($column) {
            return $this->qualifyPivotColumn($column).' as pivot_'.$column;
        })->unique()->all();
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
     * Qualify the given column name by the pivot table.
     *
     * @param  string  $column
     * @return string
     */
    public function qualifyPivotColumn($column)
    {
        return str_contains($column, '.')
            ? $column
            : $this->table.'.'.$column;
    }

    /**
     * Get the fully qualified foreign key for the relation.
     *
     * @return string
     */
    public function getQualifiedForeignPivotKeyName()
    {
        return $this->qualifyPivotColumn($this->foreignPivotKey);
    }

    /**
     * Get the fully qualified "related key" for the relation.
     *
     * @return string
     */
    public function getQualifiedRelatedPivotKeyName()
    {
        return $this->qualifyPivotColumn($this->relatedPivotKey);
    }

    /**
     * Get the fully qualified related key name for the relation.
     *
     * @return string
     */
    public function getQualifiedRelatedKeyName()
    {
        return $this->related->getTable().'.'.$this->relatedKey;
    }

    /**
     * Hydrate the pivot relationship on the models.
     *
     * @param  array  $models
     * @return void
     */
    protected function hydratePivotRelation(array $models)
    {
        foreach ($models as $model) {
            $attributes = $this->migratePivotAttributes($model);
            $pivot = $this->newExistingPivot($attributes);
            $model->setRelation($this->accessor, $pivot);
        }
    }

    /**
     * Get the pivot attributes from a model.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Alvin0\RedisModel\Model  $model
     * @return array
     */
    protected function migratePivotAttributes($model)
    {
        $values = [];

        foreach ($model->getAttributes() as $key => $value) {
            if (strpos($key, 'pivot_') === 0) {
                $values[substr($key, 6)] = $value;
                unset($model->$key);
            }
        }

        return $values;
    }

    /**
     * Specify the custom pivot table column for the created_at timestamp.
     *
     * @param  string  $createdAt
     * @return $this
     */
    public function withPivot(...$columns)
    {
        $this->pivotColumns = array_merge(
            $this->pivotColumns,
            is_array($columns[0]) ? $columns[0] : $columns
        );

        return $this;
    }

    /**
     * Specify that the pivot table has creation and update timestamps.
     *
     * @param  mixed  $createdAt
     * @param  mixed  $updatedAt
     * @return $this
     */
    public function withTimestamps($createdAt = null, $updatedAt = null)
    {
        $this->withTimestamps = true;

        $this->pivotCreatedAt = $createdAt;
        $this->pivotUpdatedAt = $updatedAt;

        return $this->withPivot($this->createdAt(), $this->updatedAt());
    }

    /**
     * Get the name of the "created at" column.
     *
     * @return string
     */
    public function createdAt()
    {
        return $this->pivotCreatedAt ?: 'created_at';
    }

    /**
     * Get the name of the "updated at" column.
     *
     * @return string
     */
    public function updatedAt()
    {
        return $this->pivotUpdatedAt ?: 'updated_at';
    }

    /**
     * Get the pivot columns for this relationship.
     *
     * @return array
     */
    public function getPivotColumns()
    {
        return $this->pivotColumns;
    }

    /**
     * Create a new pivot model instance.
     *
     * @param  array  $attributes
     * @param  bool   $exists
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

        return Pivot::fromAttributes($this->parent, $attributes, $this->table, $exists)
            ->setPivotKeys($this->foreignPivotKey, $this->relatedPivotKey);
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

        $pivot = new $class($this->parent, $attributes, $this->table, $exists);

        return $pivot->setPivotKeys($this->foreignPivotKey, $this->relatedPivotKey);
    }

    /**
     * Create a new existing pivot model instance.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Relations\Pivot
     */
    protected function newExistingPivot(array $attributes = [])
    {
        return $this->newPivot($attributes, true);
    }

    /**
     * Specify the custom pivot model to use for the relationship.
     *
     * @param  string  $class
     * @return $this
     */
    public function using($class)
    {
        $this->using = $class;
        return $this;
    }

    /**
     * Specify the custom pivot accessor to use for the relationship.
     *
     * @param  string  $accessor
     * @return $this
     */
    public function as($accessor)
    {
        $this->accessor = $accessor;
        return $this;
    }

    /**
     * Get the name of the pivot accessor for this relationship.
     *
     * @return string
     */
    public function getPivotAccessor()
    {
        return $this->accessor;
    }
} 