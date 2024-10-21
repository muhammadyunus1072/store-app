<?php

namespace App\Repositories;

abstract class MasterDataRepository
{
    const OPERATOR = ['=', '!=', '<>', '<', '<=', '>', '>='];

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

            if (isset($clause['operator']) || in_array($clause[1], self::OPERATOR)) {
                $operator = isset($clause['operator']) ? $clause['operator'] : $clause[1];
                $value = isset($clause['value']) ? $clause['value'] : $clause[2];
                $conjunction = isset($clause['conjunction']) ? isset($clause['conjunction']) : (isset($clause[3]) ? $clause[3] : null);
            } else {
                $operator = "=";
                $value = isset($clause['value']) ? $clause['value'] : $clause[1];
                $conjunction = isset($clause['conjunction']) ? isset($clause['conjunction']) : (isset($clause[2]) ? $clause[2] : null);
            }

            if ($conjunction == 'OR') {
                $query->orWhere($column, $operator, $value);
            } else {
                $query->where($column, $operator, $value);
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

    public static function findBy($whereClause)
    {
        return self::clauseProcess($whereClause)->first();
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
        $obj = self::findBy($whereClause);
        return empty($obj) ? false : $obj->delete();
    }

    public static function all()
    {
        return app(static::className())::all();
    }
}
