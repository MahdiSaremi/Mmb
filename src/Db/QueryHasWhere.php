<?php
#auto-name
namespace Mmb\Db;

use Closure;
use Mmb\Mapping\Arrayable;

trait QueryHasWhere
{

    
    /**
     * شرط ها
     *
     * @var array
     */
    protected $where = [];
    protected $currentWhereRef;

    protected function addWhere(array|Arrayable $where)
    {
        if($where instanceof Arrayable)
        {
            $where = $where->toArray();
        }

        if(isset($this->currentWhereRef))
        {
            $this->currentWhereRef[] = $where;
        }
        else
        {
            $this->where[] = $where;
        }
    }
    /**
     * افزودن شرط بصورت کد
     *
     * @param string|QueryBuilder $where
     * @param mixed ...$args
     * @return $this
     */
    public function whereRaw($where, ...$args)
    {
        if($where instanceof QueryBuilder)
        {
            $where = $where->createQuery();
            $args = [];
        }

        $this->addWhere([ 'raw', 'AND', $where, $args ]);

        return $this;
    }

    /**
     * افزودن شرط بصورت کد
     *
     * @param string|QueryBuilder $where
     * @param mixed ...$args
     * @return $this
     */
    public function andWhereRaw($where, ...$args)
    {
        if($where instanceof QueryBuilder)
        {
            $where = $where->createQuery();
            $args = [];
        }

        $this->addWhere([ 'raw', 'AND', $where, $args ]);

        return $this;
    }

    /**
     * افزودن شرط بصورت کد
     *
     * @param string|QueryBuilder $where
     * @param mixed ...$args
     * @return $this
     */
    public function orWhereRaw($where, ...$args)
    {
        if($where instanceof QueryBuilder)
        {
            $where = $where->createQuery();
            $args = [];
        }

        $this->addWhere([ 'raw', 'OR', $where, $args ]);

        return $this;
    }

    /**
     * افزودن شرط نال بودن
     *
     * @param string $col
     * @return $this
     */
    public function whereIsNull($col)
    {
        $this->addWhere([ 'isnull', 'AND', $this->stringColumn($col) ]);

        return $this;
    }

    /**
     * افزودن شرط نال بودن
     *
     * @param string $col
     * @return $this
     */
    public function andWhereIsNull($col)
    {
        $this->addWhere([ 'isnull', 'AND', $this->stringColumn($col) ]);

        return $this;
    }

    /**
     * افزودن شرط نال بودن
     *
     * @param string $col
     * @return $this
     */
    public function orWhereIsNull($col)
    {
        $this->addWhere([ 'isnull', 'OR', $this->stringColumn($col) ]);

        return $this;
    }

    /**
     * افزودن شرط نال نبودن
     *
     * @param string $col
     * @return $this
     */
    public function whereIsNotNull($col)
    {
        $this->addWhere([ 'isnotnull', 'AND', $this->stringColumn($col) ]);

        return $this;
    }

    /**
     * افزودن شرط نال نبودن
     *
     * @param string $col
     * @return $this
     */
    public function andWhereIsNotNull($col)
    {
        $this->addWhere([ 'isnotnull', 'AND', $this->stringColumn($col) ]);

        return $this;
    }

    /**
     * افزودن شرط نال نبودن
     *
     * @param string $col
     * @return $this
     */
    public function orWhereIsNotNull($col)
    {
        $this->addWhere([ 'isnotnull', 'OR', $this->stringColumn($col) ]);

        return $this;
    }

    /**
     * افزودن شرط بین ستون و مقدار
     *
     * @param string|Closure $col ستون موردنظر
     * @param string $operator نوع مقایسه / مقدار مقایسه
     * @param string $value مقدار مقایسه
     * @return $this
     */
    public function where($col, $operator = null, $value = null)
    {
        // inner condition
        if($col instanceof Closure)
        {
            $where = [];
            $this->addWhere([ 'inner', $operator ?: 'AND', &$where ]);
            
            if(isset($this->currentWhereRef))
            {
                $old = &$this->currentWhereRef;
            }
            else
            {
                $old = null;
            }

            $this->currentWhereRef = &$where;

            $col($this);

            unset($this->currentWhereRef);
            if(!is_null($old))
            {
                $this->currentWhereRef = &$old;
            }
            
            return $this;
        }

        if(count(func_get_args()) == 2)
        {    
            $value = $operator;
            $operator = '=';
        }
        
        $this->addWhere([ 'col', 'AND', $this->stringColumn($col), $operator, $value ]);

        return $this;
    }

    /**
     * افزودن شرط معکوس
     *
     * @param Closure $inner شرط
     * @param string $operator
     * @return $this
     */
    public function whereNot($inner, $operator = null)
    {
        // inner condition
        $where = [];
        $this->addWhere([ 'inner-not', $operator ?: 'AND', &$where ]);
        
        if(isset($this->currentWhereRef))
        {
            $old = &$this->currentWhereRef;
        }
        else
        {
            $old = null;
        }

        $this->currentWhereRef = &$where;

        $inner($this);

        unset($this->currentWhereRef);
        if(!is_null($old))
        {
            $this->currentWhereRef = &$old;
        }
        
        return $this;
    }

    /**
     * افزودن شرط برابری مقدار ها
     *
     * @param array|Arrayable $col_value ستون ها و مقدار مورد نیاز
     * @return $this
     */
    public function wheres(array|Arrayable $col_value)
    {
        if($col_value instanceof Arrayable)
        {
            $col_value = $col_value->toArray();
        }

        foreach($col_value as $col => $value)
        {
            $this->addWhere([ 'col', 'AND', $this->stringColumn($col), '=', $value ]);
        }

        return $this;
    }

    /**
     * افزودن شرط بین ستون و مقدار
     *
     * @param string|Closure $col ستون موردنظر
     * @param string $operator نوع مقایسه / مقدار مقایسه
     * @param string $value مقدار مقایسه
     * @return $this
     */
    public function andWhere($col, $operator = null, $value = null)
    {
        // inner condition
        if($col instanceof Closure)
        {
            return $this->where($col, 'AND');
        }

        if(count(func_get_args()) == 2) {
            
            $value = $operator;
            $operator = '=';

        }
        
        $this->addWhere([ 'col', 'AND', $this->stringColumn($col), $operator, $value ]);

        return $this;

    }

    /**
     * افزودن شرط معکوس
     *
     * @param Closure $inner
     * @return $this
     */
    public function andWhereNot($inner)
    {
        return $this->whereNot($inner, 'AND');
    }

    /**
     * افزودن شرط بین ستون و مقدار
     *
     * @param string|Closure $col ستون موردنظر
     * @param string $operator نوع مقایسه / مقدار مقایسه
     * @param string $value مقدار مقایسه
     * @return $this
     */
    public function orWhere($col, $operator = null, $value = null)
    {
        // inner condition
        if($col instanceof Closure)
        {
            return $this->where($col, 'OR');
        }

        if(count(func_get_args()) == 2) {
            
            $value = $operator;
            $operator = '=';

        }
        
        $this->addWhere([ 'col', 'OR', $this->stringColumn($col), $operator, $value ]);

        return $this;
    }

    /**
     * افزودن شرط معکوس
     *
     * @param Closure $inner
     * @return $this
     */
    public function orWhereNot($inner)
    {
        return $this->whereNot($inner, 'OR');
    }

    /**
     * افزودن شرط درونی با عملگر و
     *
     * @param Closure $callback
     * @return $this
     */
    public function and($callback)
    {
        return $this->where($callback, 'AND');
    }

    /**
     * افزودن شرط درونی با عملگر یا
     *
     * @param Closure $callback
     * @return $this
     */
    public function or($callback)
    {
        return $this->where($callback, 'OR');
    }

    /**
     * افزودن شرط معکوس درونی با عملگر و
     *
     * @param Closure $callback
     * @return $this
     */
    public function andNot($callback)
    {
        return $this->whereNot($callback, 'AND');
    }

    /**
     * افزودن شرط معکوس درونی با عملگر یا
     *
     * @param Closure $callback
     * @return $this
     */
    public function orNot($callback)
    {
        return $this->whereNot($callback, 'OR');
    }

    /**
     * افزودن شرط بین دو ستون
     *
     * @param string $col ستون موردنظر
     * @param string $operator نوع مقایسه / ستون مقایسه
     * @param string $col2 ستون مقایسه
     * @return $this
     */
    public function whereCol($col, $operator, $col2 = null)
    {
        if(count(func_get_args()) == 2) {
            
            $col2 = $operator;
            $operator = '=';

        }
        
        $this->addWhere([ 'colcol', 'AND', $this->stringColumn($col), $operator, $this->stringColumn($col2) ]);

        return $this;
    }

    /**
     * افزودن شرط بین دو ستون
     *
     * @param string $col ستون موردنظر
     * @param string $operator نوع مقایسه / ستون مقایسه
     * @param string $col2 ستون مقایسه
     * @return $this
     */
    public function andWhereCol($col, $operator, $col2 = null) {

        if(count(func_get_args()) == 2) {
            
            $col2 = $operator;
            $operator = '=';

        }
        
        $this->addWhere([ 'colcol', 'AND', $this->stringColumn($col), $operator, $this->stringColumn($col2) ]);

        return $this;

    }

    /**
     * افزودن شرط بین دو ستون
     *
     * @param string $col ستون موردنظر
     * @param string $operator نوع مقایسه / ستون مقایسه
     * @param string $col2 ستون مقایسه
     * @return $this
     */
    public function orWhereCol($col, $operator, $col2 = null) {

        if(count(func_get_args()) == 2) {
            
            $col2 = $operator;
            $operator = '=';

        }
        
        $this->addWhere([ 'colcol', 'OR', $this->stringColumn($col), $operator, $this->stringColumn($col2) ]);

        return $this;

    }

    /**
     * افزودن شرط در آرایه بودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return $this
     */
    public function whereIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        $this->addWhere([ 'in', 'AND', $this->stringColumn($col), $array ]);

        return $this;
    }

    /**
     * افزودن شرط در آرایه بودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return $this
     */
    public function andWhereIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        $this->addWhere([ 'in', 'AND', $this->stringColumn($col), $array ]);

        return $this;
    }

    /**
     * افزودن شرط در آرایه بودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return $this
     */
    public function orWhereIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        $this->addWhere([ 'in', 'OR', $this->stringColumn($col), $array ]);

        return $this;

    }

    /**
     * افزودن شرط در آرایه نبودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return $this
     */
    public function whereNotIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        $this->addWhere([ 'notin', 'AND', $this->stringColumn($col), $array ]);

        return $this;
    }

    /**
     * افزودن شرط در آرایه نبودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return $this
     */
    public function andWhereNotIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        $this->addWhere([ 'notin', 'AND', $this->stringColumn($col), $array ]);

        return $this;
    }

    /**
     * افزودن شرط در آرایه نبودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return $this
     */
    public function orWhereNotIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        $this->addWhere([ 'notin', 'OR', $this->stringColumn($col), $array ]);

        return $this;

    }

    /**
     * افزودن شرط دارا بودن یک نقش
     *
     * @param string $col ستون موردنظر
     * @param string $role نام نقش
     * @return $this
     */
    public function whereHasRole($col, $role)
    {
        $col = $this->stringColumn($col);
        return $this->whereRaw("($col = ? OR $col LIKE ? OR $col LIKE ?)", $role, "$role:%", "$role|%");
    }

    /**
     * افزودن شرط دارا بودن یک نقش
     *
     * @param string $col ستون موردنظر
     * @param string $role نام نقش
     * @return $this
     */
    public function andWhereHasRole($col, $role)
    {
        $col = $this->stringColumn($col);
        return $this->andWhereRaw("($col = ? OR $col LIKE ? OR $col LIKE ?)", $role, "$role:%", "$role|%");
    }

    /**
     * افزودن شرط دارا بودن یک نقش
     *
     * @param string $col ستون موردنظر
     * @param string $role نام نقش
     * @return $this
     */
    public function orWhereHasRole($col, $role)
    {
        $col = $this->stringColumn($col);
        return $this->orWhereRaw("($col = ? OR $col LIKE ? OR $col LIKE ?)", $role, "$role:%", "$role|%");
    }


}
