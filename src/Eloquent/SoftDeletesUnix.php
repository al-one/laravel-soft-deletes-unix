<?php

namespace Alone\LaravelSoftDeletesUnix\Eloquent;

use Illuminate\Database\Eloquent as EloquentBase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

trait SoftDeletesUnix
{

    use EloquentBase\SoftDeletes;

    /**
     * The storage format of the model's SoftDelete column.
     *
     * @var string
     */
    protected $softDeleteDateFormat = 'U';

    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootSoftDeletes()
    {
        static::addGlobalScope(new SoftDeletingUnixScope);
    }

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    protected function runSoftDelete()
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());
        $time = $this->freshTimestamp();
        $columns = [$this->getDeletedAtColumn() => $this->freshTimestampUnix($time)];
        $this->{$this->getDeletedAtColumn()} = $time;
        if ($this->timestamps && ! is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;
            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }
        $query->update($columns);
    }

    /**
     * Get a fresh timestamp for the model.
     *
     * @return int|string
     */
    public function freshTimestampUnix($time = null)
    {
        $now = isset($time) ? $time : $this->freshTimestamp();
        $fmt = $this->softDeleteDateFormat ?: $this->getDateFormat();
        return empty($now) ? $now : $this->asDateTime($now)->format($fmt);
    }

    /**
     * Instantiate a new HasManyThrough relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $farParent
     * @param  \Illuminate\Database\Eloquent\Model  $throughParent
     * @param  string  $firstKey
     * @param  string  $secondKey
     * @param  string  $localKey
     * @param  string  $secondLocalKey
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    protected function newHasManyThrough(Builder $query, Model $farParent, Model $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey)
    {
        return new HasManyThrough($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
    }

}
