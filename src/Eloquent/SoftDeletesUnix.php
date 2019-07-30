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
     * Define a has-many-through relationship.
     *
     * @version 5.3
     *
     * @param  string  $related
     * @param  string  $through
     * @param  string|null  $firstKey
     * @param  string|null  $secondKey
     * @param  string|null  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function hasManyThrough($related, $through, $firstKey = null, $secondKey = null, $localKey = null)
    {
        $through = new $through;
        $firstKey = $firstKey ?: $this->getForeignKey();
        $secondKey = $secondKey ?: $through->getForeignKey();
        $localKey = $localKey ?: $this->getKeyName();
        return new HasManyThrough((new $related)->newQuery(), $this, $through, $firstKey, $secondKey, $localKey);
    }

}
