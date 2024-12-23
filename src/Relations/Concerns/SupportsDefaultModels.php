<?php

namespace Alvin0\RedisModel\Relations\Concerns;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;

trait SupportsDefaultModels
{
    /**
     * Indicates if a default model instance should be used.
     *
     * Alternatively, may be a Closure or array.
     *
     * @var \Closure|array|bool
     */
    protected $withDefault;

    /**
     * Return a new model instance in case the relationship does not exist.
     *
     * @param  \Closure|array|bool  $callback
     * @return $this
     */
    public function withDefault($callback = true)
    {
        $this->withDefault = $callback;

        return $this;
    }

    /**
     * Get the default value for this relation.
     *
     * @param  EloquentModel|RedisModel  $parent
     * @return EloquentModel|RedisModel|null
     */
    protected function getDefaultFor(EloquentModel|RedisModel $parent)
    {
        if (! $this->withDefault) {
            return null;
        }

        $instance = $this->newRelatedInstanceFor($parent);

        if (is_callable($this->withDefault)) {
            return call_user_func($this->withDefault, $instance, $parent) ?: $instance;
        }

        if (is_array($this->withDefault)) {
            $instance->forceFill($this->withDefault);
        }

        return $instance;
    }

    /**
     * Make a new related instance for the given model.
     *
     * @param  EloquentModel|RedisModel  $parent
     * @return EloquentModel|RedisModel
     */
    abstract protected function newRelatedInstanceFor(EloquentModel|RedisModel $parent);
} 