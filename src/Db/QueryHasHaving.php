<?php
#auto-name
namespace Mmb\Db;

use Closure;
use Mmb\Mapping\Arrayable;

trait QueryHasHaving
{

    
    /**
     * شرط ها
     *
     * @var array
     */
    protected $having = [];
    protected $currentHavingRef;

    protected function addHaving(array|Arrayable $having)
    {
        if($having instanceof Arrayable)
        {
            $having = $having->toArray();
        }
        if(isset($this->currentHavingRef))
        {
            $this->currentHavingRef[] = $having;
        }
        else
        {
            $this->having[] = $having;
        }
    }
    /**
     * افزودن شرط بصورت کد
     *
     * @param string|QueryBuilder $having
     * @param mixed ...$args
     * @return $this
     */
    public function havingRaw($having, ...$args)
    {
        if($having instanceof QueryBuilder)
        {
            $having = $having->createQuery();
            $args = [];
        }

        $this->addHaving([ 'raw', 'AND', $having, $args ]);

        return $this;
    }

    /**
     * افزودن شرط بصورت کد
     *
     * @param string|QueryBuilder $having
     * @param mixed ...$args
     * @return $this
     */
    public function andHavingRaw($having, ...$args)
    {
        if($having instanceof QueryBuilder)
        {
            $having = $having->createQuery();
            $args = [];
        }

        $this->addHaving([ 'raw', 'AND', $having, $args ]);

        return $this;
    }

    /**
     * افزودن شرط بصورت کد
     *
     * @param string $having
     * @param mixed ...$args
     * @return $this
     */
    public function orHavingRaw($having, ...$args)
    {
        if($having instanceof QueryBuilder)
        {
            $having = $having->createQuery();
            $args = [];
        }

        $this->addHaving([ 'raw', 'OR', $having, $args ]);

        return $this;
    }

    /**
     * افزودن شرط نال بودن
     *
     * @param string $col
     * @return $this
     */
    public function havingIsNull($col)
    {
        $this->addHaving([ 'isnull', 'AND', $this->stringColumn($col) ]);

        return $this;
    }

    /**
     * افزودن شرط نال بودن
     *
     * @param string $col
     * @return $this
     */
    public function andHavingIsNull($col)
    {
        $this->addHaving([ 'isnull', 'AND', $this->stringColumn($col) ]);

        return $this;
    }

    /**
     * افزودن شرط نال بودن
     *
     * @param string $col
     * @return $this
     */
    public function orHavingIsNull($col)
    {
        $this->addHaving([ 'isnull', 'OR', $this->stringColumn($col) ]);

        return $this;
    }

    /**
     * افزودن شرط نال نبودن
     *
     * @param string $col
     * @return $this
     */
    public function havingIsNotNull($col)
    {
        $this->addHaving([ 'isnotnull', 'AND', $this->stringColumn($col) ]);

        return $this;
    }

    /**
     * افزودن شرط نال نبودن
     *
     * @param string $col
     * @return $this
     */
    public function andHavingIsNotNull($col)
    {
        $this->addHaving([ 'isnotnull', 'AND', $this->stringColumn($col) ]);

        return $this;
    }

    /**
     * افزودن شرط نال نبودن
     *
     * @param string $col
     * @return $this
     */
    public function orHavingIsNotNull($col)
    {
        $this->addHaving([ 'isnotnull', 'OR', $this->stringColumn($col) ]);

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
    public function having($col, $operator = null, $value = null)
    {
        // inner condition
        if($col instanceof Closure)
        {
            $having = [];
            $this->addHaving([ 'inner', $operator ?: 'AND', &$having ]);
            
            if(isset($this->currentHavingRef))
            {
                $old = &$this->currentHavingRef;
            }
            else
            {
                $old = null;
            }

            $this->currentHavingRef = &$having;

            $col($this);

            unset($this->currentHavingRef);
            if(!is_null($old))
            {
                $this->currentHavingRef = &$old;
            }
            
            return $this;
        }

        if(count(func_get_args()) == 2)
        {    
            $value = $operator;
            $operator = '=';
        }
        
        $this->addHaving([ 'col', 'AND', $this->stringColumn($col), $operator, $value ]);

        return $this;
    }

    /**
     * افزودن شرط معکوس
     *
     * @param Closure $inner شرط
     * @param string $operator
     * @return $this
     */
    public function havingNot($inner, $operator = null)
    {
        // inner condition
        $having = [];
        $this->addHaving([ 'inner-not', $operator ?: 'AND', &$having ]);
        
        if(isset($this->currentHavingRef))
        {
            $old = &$this->currentHavingRef;
        }
        else
        {
            $old = null;
        }

        $this->currentHavingRef = &$having;

        $inner($this);

        unset($this->currentHavingRef);
        if(!is_null($old))
        {
            $this->currentHavingRef = &$old;
        }
        
        return $this;
    }

    /**
     * افزودن شرط برابری مقدار ها
     *
     * @param array|Arrayable $col_value ستون ها و مقدار مورد نیاز
     * @return $this
     */
    public function havings(array|Arrayable $col_value)
    {
        if($col_value instanceof Arrayable)
        {
            $col_value = $col_value->toArray();
        }

        foreach($col_value as $col => $value)
        {
            $this->addHaving([ 'col', 'AND', $this->stringColumn($col), '=', $value ]);
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
    public function andHaving($col, $operator = null, $value = null)
    {
        // inner condition
        if($col instanceof Closure)
        {
            return $this->having($col, 'AND');
        }

        if(count(func_get_args()) == 2) {
            
            $value = $operator;
            $operator = '=';

        }
        
        $this->addHaving([ 'col', 'AND', $this->stringColumn($col), $operator, $value ]);

        return $this;

    }

    /**
     * افزودن شرط معکوس
     *
     * @param Closure $inner
     * @return $this
     */
    public function andHavingNot($inner)
    {
        return $this->havingNot($inner, 'AND');
    }

    /**
     * افزودن شرط بین ستون و مقدار
     *
     * @param string|Closure $col ستون موردنظر
     * @param string $operator نوع مقایسه / مقدار مقایسه
     * @param string $value مقدار مقایسه
     * @return $this
     */
    public function orHaving($col, $operator = null, $value = null)
    {
        // inner condition
        if($col instanceof Closure)
        {
            return $this->having($col, 'OR');
        }

        if(count(func_get_args()) == 2) {
            
            $value = $operator;
            $operator = '=';

        }
        
        $this->addHaving([ 'col', 'OR', $this->stringColumn($col), $operator, $value ]);

        return $this;
    }

    /**
     * افزودن شرط معکوس
     *
     * @param Closure $inner
     * @return $this
     */
    public function orHavingNot($inner)
    {
        return $this->havingNot($inner, 'OR');
    }

    /**
     * افزودن شرط بین دو ستون
     *
     * @param string $col ستون موردنظر
     * @param string $operator نوع مقایسه / ستون مقایسه
     * @param string $col2 ستون مقایسه
     * @return $this
     */
    public function havingCol($col, $operator, $col2 = null)
    {
        if(count(func_get_args()) == 2) {
            
            $col2 = $operator;
            $operator = '=';

        }
        
        $this->addHaving([ 'colcol', 'AND', $this->stringColumn($col), $operator, $this->stringColumn($col2) ]);

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
    public function andHavingCol($col, $operator, $col2 = null) {

        if(count(func_get_args()) == 2) {
            
            $col2 = $operator;
            $operator = '=';

        }
        
        $this->addHaving([ 'colcol', 'AND', $this->stringColumn($col), $operator, $this->stringColumn($col2) ]);

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
    public function orHavingCol($col, $operator, $col2 = null) {

        if(count(func_get_args()) == 2) {
            
            $col2 = $operator;
            $operator = '=';

        }
        
        $this->addHaving([ 'colcol', 'OR', $this->stringColumn($col), $operator, $this->stringColumn($col2) ]);

        return $this;

    }

    /**
     * افزودن شرط در آرایه بودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return $this
     */
    public function havingIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        $this->addHaving([ 'in', 'AND', $this->stringColumn($col), $array ]);

        return $this;
    }

    /**
     * افزودن شرط در آرایه بودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return $this
     */
    public function andHavingIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        $this->addHaving([ 'in', 'AND', $this->stringColumn($col), $array ]);

        return $this;
    }

    /**
     * افزودن شرط در آرایه بودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return $this
     */
    public function orHavingIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        $this->addHaving([ 'in', 'OR', $this->stringColumn($col), $array ]);

        return $this;

    }

    /**
     * افزودن شرط در آرایه نبودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return $this
     */
    public function havingNotIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        $this->addHaving([ 'notin', 'AND', $this->stringColumn($col), $array ]);

        return $this;
    }

    /**
     * افزودن شرط در آرایه نبودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return $this
     */
    public function andHavingNotIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        $this->addHaving([ 'notin', 'AND', $this->stringColumn($col), $array ]);

        return $this;
    }

    /**
     * افزودن شرط در آرایه نبودن
     *
     * @param string $col ستون موردنظر
     * @param array|Arrayable $array آرایه مقایسه
     * @return $this
     */
    public function orHavingNotIn($col, array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        $this->addHaving([ 'notin', 'OR', $this->stringColumn($col), $array ]);

        return $this;

    }

    /**
     * افزودن شرط دارا بودن یک نقش
     *
     * @param string $col ستون موردنظر
     * @param string $role نام نقش
     * @return $this
     */
    public function havingHasRole($col, $role)
    {
        $col = $this->stringColumn($col);
        return $this->havingRaw("($col = ? OR $col LIKE ? OR $col LIKE ?)", $role, "$role:%", "$role|%");
    }

    /**
     * افزودن شرط دارا بودن یک نقش
     *
     * @param string $col ستون موردنظر
     * @param string $role نام نقش
     * @return $this
     */
    public function andHavingHasRole($col, $role)
    {
        $col = $this->stringColumn($col);
        return $this->andHavingRaw("($col = ? OR $col LIKE ? OR $col LIKE ?)", $role, "$role:%", "$role|%");
    }

    /**
     * افزودن شرط دارا بودن یک نقش
     *
     * @param string $col ستون موردنظر
     * @param string $role نام نقش
     * @return $this
     */
    public function orHavingHasRole($col, $role)
    {
        $col = $this->stringColumn($col);
        return $this->orHavingRaw("($col = ? OR $col LIKE ? OR $col LIKE ?)", $role, "$role:%", "$role|%");
    }


}
