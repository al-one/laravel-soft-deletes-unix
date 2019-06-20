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
     * Get a fresh timestamp for the model.
     *
     * @return int|string
     */
    public function freshTimestampUnix()
    {
        $now = $this->freshTimestamp();
        $fmt = $this->softDeleteDateFormat ?: $this->getDateFormat();
        return empty($now) ? $now : $this->asDateTime($now)->format($fmt);
    }

}
