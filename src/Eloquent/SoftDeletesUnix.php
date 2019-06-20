<?php

namespace Alone\LaravelSoftDeletesUnix\Eloquent;

use Illuminate\Database\Eloquent as EloquentBase;

trait SoftDeletesUnix
{

    use EloquentBase\SoftDeletes;

    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootSoftDeletesUnix()
    {
        static::addGlobalScope(new SoftDeletingUnixScope);
    }

}
