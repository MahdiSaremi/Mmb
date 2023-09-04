<?php
#auto-name
namespace Mmb\Db;

use Closure;
use Mmb\Mapping\Arrayable;
use Mmb\Tools\Text;

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
    public function addWhereBefore(Closure $callback, $operator = 'AND')
    {
        if(isset($this->currentWhereRef))
        {
            $before = $this->currentWhereRef;
            if($before)
            {
                $before[0][1] = $operator;
            }

            $this->currentWhereRef = [];
            $callback($this);
            array_push($this->currentWhereRef, ...$before);
        }
        else
        {
            $before = $this->where;
            if($before)
            {
                $before[0][1] = $operator;
            }

            $this->where = [];
            $callback($this);
            array_push($this->where, ...$before);
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
     * بررسی می کند کد شما مقداری دارد یا نه
     *
     * @param string|QueryBuilder $where
     * @param mixed ...$args
     * @return $this
     */
    public function whereRawExists($where, ...$args)
    {
        if($where instanceof QueryBuilder)
        {
            $where = $where->createQuery();
            $args = [];
        }

        $this->addWhere([ 'raw-exists', 'AND', $where, $args ]);

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
     * بررسی می کند کد شما مقداری دارد یا نه
     *
     * @param string|QueryBuilder $where
     * @param mixed ...$args
     * @return $this
     */
    public function andWhereRawExists($where, ...$args)
    {
        if($where instanceof QueryBuilder)
        {
            $where = $where->createQuery();
            $args = [];
        }

        $this->addWhere([ 'raw-exists', 'AND', $where, $args ]);

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
     * افزودن شرط بصورت کد
     * 
     * بررسی می کند کد شما مقداری دارد یا نه
     *
     * @param string|QueryBuilder $where
     * @param mixed ...$args
     * @return $this
     */
    public function orWhereRawExists($where, ...$args)
    {
        if($where instanceof QueryBuilder)
        {
            $where = $where->createQuery();
            $args = [];
        }

        $this->addWhere([ 'raw-exists', 'OR', $where, $args ]);

        return $this;
    }

    /**
     * افزودن شرط داخلی
     *
     * @param QueryBuilder $inner
     * @param string|mixed $operator
     * @param mixed $value
     * @return $this
     */
    public function whereInner(QueryBuilder $inner, $operator = null, $value = null)
    {
        if(is_null($operator))
        {
            $this->whereRaw($inner);
        }
        else
        {
            $query = $inner->createQuery();
            $this->addWhere([ 'raw-operator', 'AND', $query, $operator, $value ]);
        }
        return $this;
    }

    /**
     * افزودن شرط داخلی
     *
     * @param QueryBuilder $inner
     * @param string|mixed $operator
     * @param mixed $value
     * @return $this
     */
    public function andWhereInner(QueryBuilder $inner, $operator = null, $value = null)
    {
        if(is_null($operator))
        {
            $this->andWhereRaw($inner);
        }
        else
        {
            $query = $inner->createQuery();
            $this->addWhere([ 'raw-operator', 'AND', $query, $operator, $value ]);
        }
        return $this;
    }

    /**
     * افزودن شرط داخلی
     *
     * @param QueryBuilder $inner
     * @param string|mixed $operator
     * @param mixed $value
     * @return $this
     */
    public function orWhereInner(QueryBuilder $inner, $operator = null, $value = null)
    {
        if(is_null($operator))
        {
            $this->orWhereRaw($inner);
        }
        else
        {
            $query = $inner->createQuery();
            $this->addWhere([ 'raw-operator', 'OR', $query, $operator, $value ]);
        }
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
     * @param string|mixed $operator نوع مقایسه / مقدار مقایسه
     * @param string|mixed $value مقدار مقایسه
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
     * افزودن شرط بین ستون و مقدار
     *
     * @param Closure $callback
     * @return $this
     */
    public function beforeWhere(Closure $callback, $operator = 'AND')
    {
        $this->addWhereBefore($callback, $operator);
        return $this;
    }

    /**
     * افزودن شرط معکوس
     *
     * @param Closure $inner شرط
     * @param string|mixed $operator
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
     * @param string|mixed $operator نوع مقایسه / مقدار مقایسه
     * @param string|mixed $value مقدار مقایسه
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
     * @param string|mixed $operator نوع مقایسه / مقدار مقایسه
     * @param string|mixed $value مقدار مقایسه
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
     * @param string|mixed $operator نوع مقایسه / ستون مقایسه
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
     * @param string|mixed $operator نوع مقایسه / ستون مقایسه
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
     * @param string|mixed $operator نوع مقایسه / ستون مقایسه
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
     * افزودن شرط دارا بودن یک رابطه
     *
     * @param string $relation
     * @param Closure|null $callback
     * @param string|mixed $operator
     * @param integer $count
     * @return $this
     */
    public function whereHas(string $relation, ?Closure $callback = null, $operator = '>=', $count = 1, $bool = 'AND')
    {
        $sub = false;
        if(Text::contains($relation, '.'))
        {
            $sub = Text::after($relation, '.');
            $relation = Text::before($relation, '.');
        }

        $rel = $this->getRelation($relation);
        if($sub)
        {
            $rel->addHasCondition($this, function(QueryBuilder $query) use($sub, $callback, $operator, $count)
            {
                $query->whereHas($sub, $callback, $operator, $count);
            }, bool: $bool);
        }
        else
        {
            $rel->addHasCondition($this, $callback, $operator, $count, $bool);
        }

        return $this;
    }

    /**
     * افزودن شرط دارا بودن یک رابطه
     *
     * @param string $relation
     * @param Closure|null $callback
     * @param string|mixed $operator
     * @param integer $count
     * @return $this
     */
    public function andWhereHas(string $relation, ?Closure $callback = null, $operator = '>=', $count = 1)
    {
        return $this->whereHas($relation, $callback, $operator, $count, 'AND');
    }

    /**
     * افزودن شرط دارا بودن یک رابطه
     *
     * @param string $relation
     * @param Closure|null $callback
     * @param string|mixed $operator
     * @param integer $count
     * @return $this
     */
    public function orWhereHas(string $relation, ?Closure $callback = null, $operator = '>=', $count = 1)
    {
        return $this->whereHas($relation, $callback, $operator, $count, 'OR');
    }

    /**
     * افزودن شرط دارا بودن یک رابطه
     *
     * @param string $relation
     * @param string|mixed $operator
     * @param integer $count
     * @return $this
     */
    public function has(string $relation, $operator = '>=', $count = 1)
    {
        return $this->whereHas($relation, null, $operator, $count);
    }

    /**
     * افزودن شرط دارا بودن یک رابطه
     *
     * @param string $relation
     * @param string|mixed $operator
     * @param integer $count
     * @return $this
     */
    public function andHas(string $relation, $operator = '>=', $count = 1)
    {
        return $this->andWhereHas($relation, null, $operator, $count);
    }

    /**
     * افزودن شرط دارا بودن یک رابطه
     *
     * @param string $relation
     * @param string|mixed $operator
     * @param integer $count
     * @return $this
     */
    public function orHas(string $relation, $operator = '>=', $count = 1)
    {
        return $this->orWhereHas($relation, null, $operator, $count);
    }

    /**
     * افزودن شرط دارا بودن یک نقش
     *
     * @param string $col ستون موردنظر
     * @param string $role نام نقش
     * @return $this
     */
    public function whereHasRole(string $col, string $role = null)
    {
        if(is_null($role))
        {
            $role = $col;
            $col = 'role';
        }

        $col = $this->stringColumn($col);

        $roles = explode('|', $role);

        if(count($roles) == 1)
        {
            $role = preg_quote($role);
            return $this->where($col, 'REGEXP', "(^{$role}|^([\\w\\|]*)\\|{$role})(\\:|\\||$)");
        }
        else
        {
            $this->where(function($query) use($roles) {
                foreach($roles as $role)
                {
                    $query->orWhereHasRole($role);
                }
            });

            return $this;
        }
    }

    /**
     * افزودن شرط دارا بودن یک نقش
     *
     * @param string $col ستون موردنظر
     * @param string $role نام نقش
     * @return $this
     */
    public function andWhereHasRole(string $col, string $role)
    {
        if(is_null($role))
        {
            $role = $col;
            $col = 'role';
        }

        $col = $this->stringColumn($col);

        $roles = explode('|', $role);

        if(count($roles) == 1)
        {
            $role = preg_quote($role);
            return $this->andWhere($col, 'REGEXP', "(^{$role}|^([\\w\\|]*)\\|{$role})(\\:|\\||$)");
        }
        else
        {
            $this->and(function($query) use($roles) {
                foreach($roles as $role)
                {
                    $query->orWhereHasRole($role);
                }
            });

            return $this;
        }
    }

    /**
     * افزودن شرط دارا بودن یک نقش
     *
     * @param string $col ستون موردنظر
     * @param string $role نام نقش
     * @return $this
     */
    public function orWhereHasRole(string $col, string $role)
    {
        if(is_null($role))
        {
            $role = $col;
            $col = 'role';
        }

        $col = $this->stringColumn($col);

        $roles = explode('|', $role);

        if(count($roles) == 1)
        {
            $role = preg_quote($role);
            return $this->orWhere($col, 'REGEXP', "(^{$role}|^([\\w\\|]*)\\|{$role})(\\:|\\||$)");
        }
        else
        {
            $this->or(function($query) use($roles) {
                foreach($roles as $role)
                {
                    $query->orWhereHasRole($role);
                }
            });

            return $this;
        }
    }

    /**
     * افزودن شرط دارا بودن کل نقش
     *
     * @param string $col ستون موردنظر
     * @param string $role نام نقش
     * @return $this
     */
    public function whereWithRole(string $col, string $role = null)
    {
        if(is_null($role))
        {
            $role = $col;
            $col = 'role';
        }

        $col = $this->stringColumn($col);

        $roles = explode('|', $role);

        if(count($roles) == 1)
        {
            $role = preg_quote($role);
            return $this->where($col, 'REGEXP', "(^{$role}|^([\\w\\|]*)\\|{$role})(\\:|\\||$)");
        }
        else
        {
            $this->where(function($query) use($roles) {
                foreach($roles as $role)
                {
                    $query->andWhereWithRole($role);
                }
            });

            return $this;
        }
    }

    /**
     * افزودن شرط دارا بودن کل نقش
     *
     * @param string $col ستون موردنظر
     * @param string $role نام نقش
     * @return $this
     */
    public function andWhereWithRole(string $col, string $role)
    {
        if(is_null($role))
        {
            $role = $col;
            $col = 'role';
        }

        $col = $this->stringColumn($col);

        $roles = explode('|', $role);

        if(count($roles) == 1)
        {
            $role = preg_quote($role);
            return $this->andWhere($col, 'REGEXP', "(^{$role}|^([\\w\\|]*)\\|{$role})(\\:|\\||$)");
        }
        else
        {
            $this->and(function($query) use($roles) {
                foreach($roles as $role)
                {
                    $query->andWhereWithRole($role);
                }
            });

            return $this;
        }
    }

    /**
     * افزودن شرط دارا بودن کل نقش
     *
     * @param string $col ستون موردنظر
     * @param string $role نام نقش
     * @return $this
     */
    public function orWhereWithRole(string $col, string $role)
    {
        if(is_null($role))
        {
            $role = $col;
            $col = 'role';
        }

        $col = $this->stringColumn($col);

        $roles = explode('|', $role);

        if(count($roles) == 1)
        {
            $role = preg_quote($role);
            return $this->orWhere($col, 'REGEXP', "(^{$role}|^([\\w\\|]*)\\|{$role})(\\:|\\||$)");
        }
        else
        {
            $this->or(function($query) use($roles) {
                foreach($roles as $role)
                {
                    $query->andWhereWithRole($role);
                }
            });

            return $this;
        }
    }

    /**
     * افزودن شرط نسبت داشتن به یک مدل
     *
     * @param Table\Table $model
     * @param string|null $column
     * @param string $operator
     * @return $this
     */
    public function whereRelatedTo(Table\Table $model, string $column = null, string $operator = 'AND')
    {
        $column ??= Text::snake(Text::afterLast(get_class($model), "\\")) . "_" . $model::getPrimaryKey();

        return $this->{$operator . 'where'}($column, $model->getPrimaryValue());
    }

    /**
     * افزودن شرط نسبت داشتن به یک مدل
     *
     * @param Table\Table $model
     * @param string|null $column
     * @return $this
     */
    public function andWhereRelatedTo(Table\Table $model, string $column = null)
    {
        return $this->whereRelatedTo($model, $column, 'AND');
    }

    /**
     * افزودن شرط نسبت داشتن به یک مدل
     *
     * @param Table\Table $model
     * @param string|null $column
     * @return $this
     */
    public function orWhereRelatedTo(Table\Table $model, string $column = null)
    {
        return $this->whereRelatedTo($model, $column, 'OR');
    }

}
