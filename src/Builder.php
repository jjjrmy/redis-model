<?php
namespace Alvin0\RedisModel;

use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Support\Facades\DB;
use Alvin0\RedisModel\Traits\QueriesRedisRelationships;

class Builder
{
    use QueriesRedisRelationships;

    /**
     * @var PhpRedisConnection
     */
    protected $connection;

    /**
     * @var \Alvin0\RedisModel\Model
     */
    protected $model;

    /**
     * @var \Alvin0\RedisModel\RedisRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $hashPattern = "*";

    /**
     * @var array
     */
    protected $conditionSession = [];

    /**
     * The relationships that should be eager loaded.
     *
     * @var array
     */
    protected $eagerLoad = [];

    /**
     * @var array
     */
    protected $whereInConditions = [];

    /**
     * Create a new query builder instance.
     *
     * @param  PhpRedisConnection  $connection
     * @return void
     */
    public function __construct(PhpRedisConnection $connection)
    {
        $this->connection = $connection;
        $this->repository = new RedisRepository($connection);
    }

    /**
     * Get the model instance being queried.
     *
     * @return \Alvin0\RedisModel\RedisRepository|static
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Get the model instance being queried.
     *
     * @return \Alvin0\RedisModel\Model|static
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set a model instance for the model being queried.
     *
     * @param  \Alvin0\RedisModel\Model  $model
     *
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
        $this->setHashPattern($model->getTable() . ":*");

        return $this;
    }

    /**
     * Set the table which the query is targeting.
     *
     * @param  string  $table
     * @return $this
     */
    public function from($table)
    {
        $this->table = $table;
        $this->repository->setTable($table);
        return $this;
    }

    /**
     * Insert new records into the database.
     *
     * @param  array  $values
     * @return bool
     */
    public function insert(array $values)
    {
        return DB::table($this->table)->insert($values);
    }

    /**
     * Set the hash pattern to search for in Redis
     *
     * @param string $hashPattern The hash pattern to search for
     *
     * @return $this
     */
    public function setHashPattern(string $hashPattern)
    {
        $this->hashPattern = $hashPattern;

        return $this;
    }

    /**
     *Get the hash pattern that is being searched for in Redis

     *@return string The hash pattern being searched for
     */
    public function getHashPattern()
    {
        return $this->hashPattern;
    }

    /**
     * Set the session condition for the search
     *
     * @param array $condition An array of conditions to search for
     *
     *@return void
     */
    public function setConditionSession(array $condition)
    {
        $this->conditionSession = $condition;
    }

    /**
     * @param array $condition
     *
     * @return array
     */
    public function getConditionSession()
    {
        return $this->conditionSession;
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param  string|array  $column
     * @param  string  $value
     * @return $this
     */
    public function where(string | array $column, string | int $value = null)
    {
        if ($value !== null && gettype($column) === 'string') {
            // Cast the value using the model's cast method if available
            if (method_exists($this->model, 'castAttributeBeforeSave')) {
                $value = $this->model->castAttributeBeforeSave($column, $value);
            }
            $this->setConditionSession(array_merge($this->getConditionSession(), [$column => $value]));
        } else if (gettype($column) === 'array') {
            $conditions = [];
            foreach ($column as $key => $val) {
                // Cast each value using the model's cast method if available
                if (method_exists($this->model, 'castAttributeBeforeSave')) {
                    $val = $this->model->castAttributeBeforeSave($key, $val);
                }
                $conditions[$key] = $val;
            }
            $this->setConditionSession(array_merge($this->getConditionSession(), $conditions));
        }

        $pattern = $this->compileHashByFields($this->getConditionSession());
        $this->setHashPattern($pattern);

        return $this;
    }
    /**
     * Add a where clause on the primary key to the query.
     *
     * @param  string  $id
     *
     * @return $this
     */
    public function whereKey($id)
    {
        if ($id !== null && $this->model->getKeyType() === 'string') {
            $id = (string) $id;
        }

        return $this->where($this->model->getQualifiedKeyName(), $id);
    }

    /**
     * Add a basic where clause to the query, and return the first result.
     *
     * @param  string|array  $column
     *
     * @param  string  $value
     *
     * @return \Alvin0\RedisModel\Model|static|null
     */
    public function firstWhere(string | array $column, string | int $value = null)
    {
        return $this->where(...func_get_args())->first();
    }

    /**
     * Execute the query and get the first result.
     *
     * @param  array|string  $columns
     *
     * @return \Alvin0\RedisModel\Model|null
     */
    public function first()
    {
        return $this->get()->first();
    }

    /**
     * Execute the destroy data for pattern keys.
     *
     * @return bool
     */
    public function destroy()
    {
        $keys = $this->getRepository()->getHashByPattern($this->getHashPattern());

        return $this->getRepository()->destroyHash($keys);
    }

    /**
     * Execute the fetch properties for keys.
     *
     * @param  array|string  $columns
     * The columns to be fetched from Redis. Can be an array of string or a string.
     *
     * @return \Alvin0\RedisModel\Collection|static[]
     *  Returns a collection of model instances or an empty collection if no result is found.
     */
    public function get($columns = ['*'])
    {
        $models = [];

        if (!empty($this->whereInConditions)) {
            // Handle whereIn conditions
            foreach ($this->whereInConditions as $condition) {
                foreach ($condition['values'] as $value) {
                    // Create a new query for each value
                    $query = clone $this;
                    // Reset the query state
                    $query->whereInConditions = [];
                    $query->conditionSession = [];
                    // Add the where condition
                    $query->where($condition['column'], $value);
                    
                    // Get the results for this value
                    $results = $query->getModels($columns);
                    if (!empty($results)) {
                        foreach ($results as $result) {
                            $models[] = $result;
                        }
                    }
                }
            }
        } else {
            // Normal query without whereIn
            $models = $this->getModels($columns);
        }

        if (count($models) > 0) {
            $models = $this->eagerLoadRelations($models);
        }

        return $this->model->newCollection($models);
    }

    /**
     * Eager load the relationships for the models.
     *
     * @param  array  $models
     * @return array
     */
    public function eagerLoadRelations(array $models)
    {
        foreach ($this->eagerLoad as $name => $constraints) {
            if (method_exists($this->model, $name)) {
                $relation = $this->model->$name();
                $relation->addEagerConstraints($models);
                $results = $relation->getEager();
                $relation->match($models, $results, $name);
            }
        }

        return $models;
    }

    /**
     * Counts the number of records that match the hash pattern of the model.
     *
     * @return int The number of records that match the hash pattern.
     */
    public function count()
    {
        return $this->getRepository()->countByPattern($this->getHashPattern());
    }

    /**
     * Create a new Collection instance with the given models.
     *
     * @param array $models
     *
     * @return \Alvin0\RedisModel\Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }

    /**
     * Create a new instance of the model being queried.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function newModelInstance($attributes = [])
    {
        return $this->model->newInstance($attributes);
    }

    /**
     * Find a model by its primary key.
     *
     * @param mixed $id The primary key value of the model to find.
     * @return \Alvin0\RedisModel\Model|null The found model or null if not found.
     */
    public function find($id)
    {
        // Retrieves the first model that matches the specified primary key.
        $model = $this->whereKey($id)->first();

        // If the model is found, sync its original state and return a clone of it.
        if ($model instanceof Model) {
            $model->syncOriginal();

            return clone ($model);
        }

        return null;
    }

    /**
     * Save a new model and return the instance.
     *
     * @param  array  $attributes - The attributes to create the model with.
     *
     * @return \Alvin0\RedisModel\Model|$this - The newly created model instance.
     */
    public function create(array $attributes = [])
    {
        return tap($this->newModelInstance($attributes), function ($instance) {
            $instance->save();
        });
    }

    /**
     * Save a new model and return the instance. Allow mass-assignment.
     *
     * @param array $attributes The attributes to be saved.
     *
     * @return \Alvin0\RedisModel\Model|$this The created model instance.
     */
    public function forceCreate(array $attributes)
    {
        return tap($this->newModelInstance($attributes), function ($instance) {
            $instance->setPrioritizeForceSave();
            $instance->save();
        });
    }

    /**
     * Chunk the results of the query.
     *
     * @param int $count The number of models to retrieve per chunk
     * @param callable|null $callback Optional callback function to be executed on each chunk
     * @return \Alvin0\RedisModel\Collection A collection of the retrieved models, chunked
     */
    public function chunk($count, callable $callback = null)
    {
        $resultData = $this->newCollection([]);

        // Scan for the models in the Redis database, and execute the provided callback function (if any) on each chunk
        $this->getRepository()
            ->scanByHash(
                $this->getHashPattern(),
                $count,
                function ($keys) use ($callback, $resultData) {
                    $modelsChunk = [];

                    // Fetch the attributes of the models in the current chunk, and create new model instances with
                    // these attributes
                    foreach ($this->getRepository()->fetchProperByListHash($keys) as $hash => $attributes) {
                        $modelsChunk[] = $this->model->newInstance($attributes, true, $hash, true)->syncOriginal();
                    }

                    $resultData->push($modelsChunk);

                    // Execute the provided callback function (if any) on the current chunk of models
                    $callback == null ?: $callback($modelsChunk);
                }
            );

        return $resultData;
    }

    /**
     * Checks if a hash record exists in Redis based on the given model attributes.
     *
     * @param array $attributes The attributes to check in the hash record.
     *
     * @return bool Returns true if a hash record exists in Redis for the given attributes, false otherwise.
     */
    public function isExists(array $attributes)
    {
        return empty($this->getRepository()->getHashByPattern($this->compileHashByFields($attributes))) ? false : true;
    }

    /**
     * Compile a hash key by fields of the given attributes array.
     *
     * @param array $attributes The array of attributes.
     *
     * @return string The compiled hash key.
     */
    public function compileHashByFields(array $attributes)
    {
        $listKey = array_merge([$this->model->getKeyName()], $this->model->getSubKeys());
        $stringKey = '';

        foreach ($listKey as $key) {
            $attributeValue = $attributes[$key] ?? '*';
            $stringKey .= $key . ':' . ($attributeValue === '*' ? '*' : $this->model->castAttributeBeforeSave($key, $attributeValue)) . ':';
        }

        return $this->model->getTable() . ":" . rtrim($stringKey, ':');
    }

    /**
     * Get the underlying query builder instance.
     * This is required for pivot table operations.
     *
     * @return $this
     */
    public function getQuery()
    {
        return $this;
    }

    /**
     * Apply the scopes to the Eloquent builder instance and return it.
     *
     * @return $this
     */
    public function applyScopes()
    {
        return $this;
    }

    /**
     * Add a select statement to the query.
     *
     * @param  array|mixed  $columns
     * @return $this
     */
    public function addSelect($columns)
    {
        return $this;
    }

    /**
     * Get the columns for the query.
     *
     * @return array
     */
    public function getColumns()
    {
        return [];
    }

    /**
     * Create a new query instance for pivot query.
     *
     * @return $this
     */
    public function newQuery()
    {
        return new static($this->connection);
    }

    /**
     * Set the relationships that should be eager loaded.
     *
     * @param  string|array  $relations
     * @return $this
     */
    public function with($relations)
    {
        $eagerLoad = $this->parseWithRelations(is_string($relations) ? func_get_args() : $relations);
        
        $this->eagerLoad = array_merge($this->eagerLoad, $eagerLoad);
        
        return $this;
    }

    /**
     * Parse a list of relations into individuals.
     *
     * @param  array  $relations
     * @return array
     */
    protected function parseWithRelations(array $relations)
    {
        $results = [];

        foreach ($relations as $name => $constraints) {
            if (is_numeric($name)) {
                $name = $constraints;
                $constraints = function () {};
            }

            $results[$name] = $constraints;
        }

        return $results;
    }

    /**
     * Get the hydrated models without eager loading.
     *
     * @param  array  $columns
     * @return \Alvin0\RedisModel\Model[]
     */
    public function getModels($columns = ['*'])
    {
        $models = [];

        foreach ($this->getRepository()->fetchHashDataByPattern($this->getHashPattern()) as $hash => $attributes) {
            $models[] = $this->model->newInstance($attributes, true, $hash, true)->syncOriginal();
        }

        return $models;
    }

    /**
     * Get the first record matching the attributes or create it.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Alvin0\RedisModel\Model
     */
    public function firstOrCreate(array $attributes = [], array $values = [])
    {
        $model = $this->where($attributes)->first();

        if ($model) {
            return $model;
        }

        return $this->create($values);
    }

    /**
     * @param string $column
     * @param array $values
     * @param string $boolean
     *
     * @return $this
     */
    public function whereIn($column, array $values, $boolean = 'and')
    {
        // Store the whereIn conditions for later execution
        $this->whereInConditions[] = [
            'column' => $column,
            'values' => array_map(function ($value) use ($column) {
                // Cast the value using the model's cast method if available
                if (method_exists($this->model, 'castAttributeBeforeSave')) {
                    return $this->model->castAttributeBeforeSave($column, $value);
                }
                return $value;
            }, $values),
            'boolean' => $boolean,
        ];

        return $this;
    }
}
