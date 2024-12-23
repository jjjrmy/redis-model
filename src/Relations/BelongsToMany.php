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
     * The keys of the parent models.
     *
     * @var array
     */
    protected $eagerKeys = [];

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
        $this->eagerKeys = collect($models)->map(function ($model) {
            return $model->{$this->parentKey};
        })->all();
    }

    /**
     * Get the results of the relationship.
     *
     * @return Collection|RedisCollection
     */
    public function get($columns = ['*'])
    {
        if ($this->parent instanceof RedisModel || $this->related instanceof RedisModel) {
            $parentKey = $this->parent->{$this->parentKey};
            $relatedIds = $this->getPivotIds($parentKey);
            
            if (empty($relatedIds)) {
                return $this->related instanceof RedisModel 
                    ? new RedisCollection 
                    : new Collection;
            }

            $results = $this->related instanceof RedisModel 
                ? new RedisCollection 
                : new Collection;

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

        return parent::get($columns);
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

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array<int, TDeclaringModel>  $models
     * @param  Collection|RedisCollection  $results
     * @param  string  $relation
     * @return array<int, TDeclaringModel>
     */
    public function match(array $models, $results, $relation)
    {
        if ($this->parent instanceof RedisModel || $this->related instanceof RedisModel) {
            $dictionary = $this->buildDictionary($results);

            foreach ($models as $model) {
                $key = (string) $model->{$this->parentKey};

                if (isset($dictionary[$key])) {
                    $model->setRelation($relation, $dictionary[$key]);
                } else {
                    $model->setRelation($relation, $this->related->newCollection());
                }
            }

            return $models;
        }

        return parent::match($models, $results, $relation);
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @param  Collection|RedisCollection  $results
     * @return array<string, Collection|RedisCollection>
     */
    protected function buildDictionary($results)
    {
        $dictionary = [];

        foreach ($results as $result) {
            $key = (string) $result->pivot->{$this->foreignPivotKey};

            if (! isset($dictionary[$key])) {
                $dictionary[$key] = $this->related instanceof RedisModel 
                    ? new RedisCollection 
                    : new Collection;
            }

            $dictionary[$key]->push($result);
        }

        return $dictionary;
    }

    /**
     * Get the eager loads for the relation.
     *
     * @return Collection<int, TRelatedModel>
     */
    public function getEager()
    {
        if ($this->parent instanceof RedisModel || $this->related instanceof RedisModel) {
            $results = $this->related instanceof RedisModel 
                ? new RedisCollection 
                : new Collection;
            
            foreach ($this->eagerKeys as $key) {
                $relatedIds = $this->getPivotIds($key);
                
                foreach ($relatedIds as $id) {
                    if ($model = $this->related::find($id)) {
                        $pivotData = $this->getPivotData($key, $id);
                        $pivot = $this->newExistingPivot($pivotData);
                        
                        $model->setRelation($this->accessor, $pivot);
                        $results->push($model);
                    }
                }
            }
            
            return $results;
        }

        return new Collection(parent::getEager());
    }
} 