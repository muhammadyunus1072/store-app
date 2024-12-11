<?php

namespace App\Repositories;

abstract class MasterDataRepository
{
    const OPERATOR = ['=', '!=', '<>', '<', '<=', '>', '>=', 'IN', 'NOT IN'];

    abstract protected static function className(): string;

    public static function create($data)
    {
        return app(static::className())::create($data);
    }

    public static function clauseProcess($whereClause, $orderByClause = null)
    {
        $query = app(static::className())->query();
        foreach ($whereClause as $clause) {
            $column = isset($clause['column']) ? $clause['column'] : $clause[0];
            if (isset($clause['operator']) || in_array($clause[1], self::OPERATOR, true)) {
                $operator = isset($clause['operator']) ? $clause['operator'] : $clause[1];
                $value = isset($clause['value']) ? $clause['value'] : $clause[2];
                $conjunction = isset($clause['conjunction']) ? isset($clause['conjunction']) : (isset($clause[3]) ? $clause[3] : null);
            } else {
                $operator = is_array($clause[1]) ? "IN" : "=";
                $value = isset($clause['value']) ? $clause['value'] : $clause[1];
                $conjunction = isset($clause['conjunction']) ? isset($clause['conjunction']) : (isset($clause[2]) ? $clause[2] : null);
            }

            if ($conjunction == 'OR') {
                if ($operator == "IN") {
                    $query->orWhereIn($column, $value);
                } else if ($operator == "NOT IN") {
                    $query->orWhereNotIn($column, $value);
                } else {
                    $query->orWhere($column, $operator, $value);
                }
            } else {
                if ($operator == "IN") {
                    $query->whereIn($column, $value);
                } else if ($operator == "NOT IN") {
                    $query->whereNotIn($column, $value);
                } else {
                    $query->where($column, $operator, $value);
                }
            }
        }

        if (is_array($orderByClause)) {
            foreach ($orderByClause as $clause) {
                $column = isset($clause['column']) ? $clause['column'] : $clause[0];

                $direction = 'ASC';
                if (isset($clause['direction']) || isset($clause[1])) {
                    $direction = isset($clause['direction']) ? $clause['direction'] : $clause[1];
                }

                $query->orderBy($column, $direction);
            }
        }

        return $query;
    }

    public static function getBy($whereClause, $orderByClause = null)
    {
        return self::clauseProcess($whereClause, $orderByClause)->get();
    }

    public static function count()
    {
        return app(static::className())->count();
    }

    public static function countBy($whereClause)
    {
        return self::clauseProcess($whereClause)->count();
    }

    public static function find($id)
    {
        return app(static::className())->find($id);
    }

    public static function findBy($whereClause, $lockForUpdate = false)
    {
        return self::clauseProcess($whereClause)
            ->when($lockForUpdate, function ($query) {
                $query->lockForUpdate();
            })
            ->first();
    }

    public static function update($id, $data)
    {
        $obj = self::find($id);
        return empty($obj) ? false : $obj->update($data);
    }

    public static function updateBy($whereClause, $data)
    {
        $obj = self::findBy($whereClause);
        return empty($obj) ? false : $obj->update($data);
    }

    public static function delete($id)
    {
        $obj = self::find($id);
        return empty($obj) ? false : $obj->delete();
    }

    public static function deleteBy($whereClause)
    {
        $objs = self::getBy($whereClause);
        foreach ($objs as $obj) {
            $obj->delete();
        }
        return count($objs) > 0;
    }

    public static function forceDelete($id)
    {
        $obj = self::find($id);
        return empty($obj) ? false : $obj->forceDelete();
    }

    public static function forceDeleteBy($whereClause)
    {
        $objs = self::getBy($whereClause);
        foreach ($objs as $obj) {
            $obj->forceDelete();
        }
        return count($objs) > 0;
    }

    public static function all()
    {
        return app(static::className())::all();
    }
}
