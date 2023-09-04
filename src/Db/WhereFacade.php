<?php
#auto-name
namespace Mmb\Db;

use Mmb\Mapping\Arrayable;

class WhereFacade
{

    /**
     * شرط بصورت کد
     *
     * @param string|QueryBuilder $where
     * @param mixed ...$args
     * @return array
     */
    public static function whereRaw($where, ...$args)
    {
        if($where instanceof QueryBuilder)
        {
            $where = $where->createQuery();
            $args = [];
        }

        return [ 'raw', 'AND', $where, $args ];
    }

    /**
     * شرط بصورت کد
     *
     * @param string|QueryBuilder $where
     * @param mixed ...$args
     * @return array
     */
    public static function andWhereRaw($where, ...$args)
    {
        if($where instanceof QueryBuilder)
        {
            $where = $where->createQuery();
            $args = [];
        }

        return [ 'raw', 'AND', $where, $args ];
    }

    /**
     * شرط بصورت کد
     *
     * @param string|QueryBuilder $where
     * @param mixed ...$args
     * @return array
     */
    public static function orWhereRaw($where, ...$args)
    {
        if($where instanceof QueryBuilder)
        {
            $where = $where->createQuery();
            $args = [];
        }

        return [ 'raw', 'OR', $where, $args ];
    }

    /**
     * شرط نال بودن
     *
     * @param string $col
     * @return array
     */
    public static function whereIsNull($col)
    {
        return [ 'isnull', 'AND', QueryBuilder::stringColumn($col) ];
    }

    /**
     * شرط نال بودن
     *
     * @param string $col
     * @return array
     */
    public static function andWhereIsNull($col)
    {
        return [ 'isnull', 'AND', QueryBuilder::stringColumn($col) ];
    }

    /**
     * شرط نال بودن
     *
     * @param string $col
     * @return array
     */
    public static function orWhereIsNull($col)
    {
        return [ 'isnull', 'OR', QueryBuilder::stringColumn($col) ];
    }

    /**
     * شرط نال نبودن
     *
     * @param string $col
     * @return array
     */
    public static function whereIsNotNull($col)
    {
        return [ 'isnotnull', 'AND', QueryBuilder::stringColumn($col) ];
    }

    /**
     * شرط نال نبودن
     *
     * @param string $col
     * @return array
     */
    public static function andWhereIsNotNull($col)
    {
        return [ 'isnotnull', 'AND', QueryBuilder::stringColumn($col) ];
    }

    /**
     * شرط نال نبودن
     *
     * @param string $col
     * @return array
     */
    public static function orWhereIsNotNull($col)
    {
        return [ 'isnotnull', 'OR', QueryBuilder::stringColumn($col) ];
    }

    /**
     * شرط بین ستون و مقدار
     *
     * @param string|array $col ستون موردنظر
     * @param string $operator نوع مقایسه / مقدار مقایسه
     * @param string $value مقدار مقایسه
     * @return array
     */
    public static function where($col, $operator = null, $value = null)
    {
        // inner condition
        if(is_array($col))
        {
            return [ 'inner', $operator ?: 'AND', $col ];
        }

        if(count(func_get_args()) == 2)
        {    
            $value = $operator;
            $operator = '=';
        }
        
        return [ 'col', 'AND', QueryBuilder::stringColumn($col), $operator, $value ];
    }

    /**
     * شرط معکوس
     *
     * @param array $inner شرط
     * @param string $operator
     * @return array
     */
    public static function whereNot(array $inner, $operator = null)
    {
        return [ 'inner-not', $operator ?: 'AND', $inner ];
    }

    /**
     * شرط برابری مقدار ها
     *
     * @param array|Arrayable $col_value ستون ها و مقدار مورد نیاز
     * @return array
     */
    public static function wheres(array|Arrayable $col_value)
    {
        if($col_value instanceof Arrayable)
        {
            $col_value = $col_value->toArray();
        }

        $wheres = [];

        foreach($col_value as $col => $value)
        {
            $wheres[] = [ 'col', 'AND', QueryBuilder::stringColumn($col), '=', $value ];
        }

        return $wheres;
    }

    /**
     * شرط بین ستون و مقدار
     *
     * @param string|array $col ستون موردنظر
     * @param string $operator نوع مقایسه / مقدار مقایسه
     * @param string $value مقدار مقایسه
     * @return array
     */
    public static function andWhere($col, $operator = null, $value = null)
    {
        // inner condition
        if(is_array($col))
        {
            return static::where($col, 'AND');
        }

        if(count(func_get_args()) == 2)
        {   
            $value = $operator;
            $operator = '=';
        }
        
        return [ 'col', 'AND', QueryBuilder::stringColumn($col), $operator, $value ];
    }

    /**
     * شرط معکوس
     *
     * @param array $inner
     * @return array
     */
    public static function andWhereNot(array $inner)
    {
        return static::whereNot($inner, 'AND');
    }

    /**
     * شرط بین ستون و مقدار
     *
     * @param string|array $col ستون موردنظر
     * @param string $operator نوع مقایسه / مقدار مقایسه
     * @param string $value مقدار مقایسه
     * @return array
     */
    public static function orWhere($col, $operator = null, $value = null)
    {
        // inner condition
        if(is_array($col))
        {
            return static::where($col, 'OR');
        }

        if(count(func_get_args()) == 2) {
            
            $value = $operator;
            $operator = '=';

        }
        
        return [ 'col', 'OR', QueryBuilder::stringColumn($col), $operator, $value ];
    }

    /**
     * شرط معکوس
     *
     * @param array $inner
     * @return array
     */
    public static function orWhereNot(array $inner)
    {
        return static::whereNot($inner, 'OR');
    }

    /**
     * شرط درونی با عملگر و
     *
     * @param array $inner
     * @return array
     */
    public static function and(array $inner)
    {
        return static::where($inner, 'AND');
    }

    /**
     * شرط درونی با عملگر یا
     *
     * @param array $inner
     * @return array
     */
    public static function or(array $inner)
    {
        return static::where($inner, 'OR');
    }

    /**
     * شرط معکوس درونی با عملگر و
     *
     * @param array $inner
     * @return array
     */
    public static function andNot(array $inner)
    {
        return static::whereNot($inner, 'AND');
    }

    /**
     * شرط معکوس درونی با عملگر یا
     *
     * @param array $inner
     * @return array
     */
    public static function orNot(array $inner)
    {
        return static::whereNot($inner, 'OR');
    }

    /**
     * شرط بین دو ستون
     *
     * @param string $col ستون موردنظر
     * @param string $operator نوع مقایسه / ستون مقایسه
     * @param string $col2 ستون مقایسه
     * @return array
     */
    public static function whereCol($col, $operator, $col2 = null)
    {
        if(count(func_get_args()) == 2)
        {
            $col2 = $operator;
            $operator = '=';
        }
        
        return [ 'colcol', 'AND', QueryBuilder::stringColumn($col), $operator, QueryBuilder::stringColumn($col2) ];
    }

    /**
     * شرط بین دو ستون
     *
     * @param string $col ستون موردنظر
     * @param string $operator نوع مقایسه / ستون مقایسه
     * @param string $col2 ستون مقایسه
     * @return array
     */
    public static function andWhereCol($col, $operator, $col2 = null)
    {
        if(count(func_get_args()) == 2)
        {
            $col2 = $operator;
            $operator = '=';
        }
        
        return [ 'colcol', 'AND', QueryBuilder::stringColumn($col), $operator, QueryBuilder::stringColumn($col2) ];
    }

    /**
     * شرط بین دو ستون
     *
     * @param string $col ستون موردنظر
     * @param string $operator نوع مقایسه / ستون مقایسه
     * @param string $col2 ستون مقایسه
     * @return array
     */
    public static function orWhereCol($col, $operator, $col2 = null)
    {
        if(count(func_get_args()) == 2) {
            
            $col2 = $operator;
            $operator = '=';

        }
        
        return [ 'colcol', 'OR', QueryBuilder::stringColumn($col), $operator, QueryBuilder::stringColumn($col2) ];
    }

    /**
     * شرط در آرایه بودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return array
     */
    public static function whereIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        return [ 'in', 'AND', QueryBuilder::stringColumn($col), $array ];
    }

    /**
     * شرط در آرایه بودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return array
     */
    public static function andWhereIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        return [ 'in', 'AND', QueryBuilder::stringColumn($col), $array ];
    }

    /**
     * شرط در آرایه بودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return array
     */
    public static function orWhereIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        return [ 'in', 'OR', QueryBuilder::stringColumn($col), $array ];
    }

    /**
     * شرط در آرایه نبودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return array
     */
    public static function whereNotIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        return [ 'notin', 'AND', QueryBuilder::stringColumn($col), $array ];
    }

    /**
     * شرط در آرایه نبودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return array
     */
    public static function andWhereNotIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        return [ 'notin', 'AND', QueryBuilder::stringColumn($col), $array ];
    }

    /**
     * شرط در آرایه نبودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return array
     */
    public static function orWhereNotIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        return [ 'notin', 'OR', QueryBuilder::stringColumn($col), $array ];
    }

}
