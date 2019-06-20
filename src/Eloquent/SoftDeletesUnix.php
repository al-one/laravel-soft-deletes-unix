<?php

namespace Alone\LaravelSoftDeletesUnix\Eloquent;

use Illuminate\Database\Eloquent as EloquentBase;

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

}
