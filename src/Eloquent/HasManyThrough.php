<?php

namespace Alone\LaravelSoftDeletesUnix\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\SoftDeletes;

class HasManyThrough extends Relations\HasManyThrough
{

    /**
     * Determine whether "through" parent of the relation uses Soft Deletes.
     *
     * @return bool
     */
    public function throughParentSoftDeletesUnix()
    {
        return in_array(SoftDeletesUnix::class, class_uses_recursive($this->throughParent));
    }

    /**
     * Determine whether close parent of the relation uses Soft Deletes.
     *
     * @version 5.3-
     * @deprecated 5.4
     *
     * @return bool
     */
    public function parentSoftDeletes()
    {
        return in_array(SoftDeletes::class, class_uses_recursive(get_class($this->parent)));
    }

    /**
     * Set the join clause on the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|null  $query
     * @return void
     */
    protected function performJoin(Builder $query = null)
    {
        $query = $query ?: $this->query;

        $farKey = $this->getQualifiedFarKeyName();

        $query->join($this->throughParent->getTable(), $this->getQualifiedParentKeyName(), '=', $farKey);

        if ($this->throughParentSoftDeletesUnix()) {
            $query->where($column = $this->throughParent->getQualifiedDeletedAtColumn(),0)
                ->orWhere($column,'>',(int)$this->throughParent->freshTimestampUnix());
        }
        elseif ($this->throughParentSoftDeletes()) {
            $query->whereNull($this->throughParent->getQualifiedDeletedAtColumn());
        }
    }

    /**
     * Set the join clause on the query.
     *
     * @version 5.3-
     * @deprecated 5.4
     *
     * @param  \Illuminate\Database\Eloquent\Builder|null  $query
     * @return void
     */
    protected function setJoin(Builder $query = null)
    {
        $query = $query ?: $this->query;
        $foreignKey = $this->related->getTable().'.'.$this->secondKey;
        $query->join($this->parent->getTable(), $this->getQualifiedParentKeyName(), '=', $foreignKey);
        if ($this->throughParentSoftDeletesUnix()) {
            $query->where($column = $this->throughParent->getQualifiedDeletedAtColumn(),0)
                ->orWhere($column,'>',(int)$this->throughParent->freshTimestampUnix());
        }
        elseif ($this->parentSoftDeletes()) {
            $query->whereNull($this->parent->getQualifiedDeletedAtColumn());
        }
    }


    /**
     * Add the constraints for a relationship query on the same table.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder  $parentQuery
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query->from($query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash());

        $query->join($this->throughParent->getTable(), $this->getQualifiedParentKeyName(), '=', $hash.'.'.$this->secondKey);

        if ($this->throughParentSoftDeletesUnix()) {
            $query->where($column = $this->throughParent->getQualifiedDeletedAtColumn(),0)
                ->orWhere($column,'>',(int)$this->throughParent->freshTimestampUnix());
        }
        elseif ($this->throughParentSoftDeletes()) {
            $query->whereNull($this->throughParent->getQualifiedDeletedAtColumn());
        }

        $query->getModel()->setTable($hash);

        return $query->select($columns)->whereColumn(
            $parentQuery->getQuery()->from.'.'.$this->localKey, '=', $this->getQualifiedFirstKeyName()
        );
    }

    /**
     * Add the constraints for a relationship query on the same table as the through parent.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder  $parentQuery
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceQueryForThroughSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $table = $this->throughParent->getTable().' as '.$hash = $this->getRelationCountHash();

        $query->join($table, $hash.'.'.$this->secondLocalKey, '=', $this->getQualifiedFarKeyName());

        if ($this->throughParentSoftDeletesUnix()) {
            $query->where($column = $this->throughParent->getQualifiedDeletedAtColumn(),0)
                ->orWhere($column,'>',(int)$this->throughParent->freshTimestampUnix());
        }
        elseif ($this->throughParentSoftDeletes()) {
            $query->whereNull($hash.'.'.$this->throughParent->getDeletedAtColumn());
        }

        return $query->select($columns)->whereColumn(
            $parentQuery->getQuery()->from.'.'.$this->localKey, '=', $hash.'.'.$this->firstKey
        );
    }

}
