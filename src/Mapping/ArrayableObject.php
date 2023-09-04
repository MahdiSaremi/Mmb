<?php
#auto-name
namespace Mmb\Mapping;

use ArrayAccess;
use ArrayObject;
use Closure;
use Countable;
use Iterator;
use IteratorAggregate;
use Mmb\Big\BigNumber;
use Mmb\Exceptions\MmbException;
use Mmb\Tools\ATool;
use Mmb\Tools\Operator;

/**
 * @template V
 * @implements Arrayable<V>
 * @implements ArrayAccess<int|string,V>
 * @implements IteratorAggregate<int|string,V>
 */
abstract class ArrayableObject implements Arrayable, Countable, ArrayAccess, IteratorAggregate
{

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    
    #region Interface methods

    public function count()
    {
        return count($this->data);
    }

	public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
	}
	
    /**
     * @return V
     */
	public function offsetGet($offset)
    {
        return $this->data[$offset];
	}

    /**
     * @return V|mixed
     */
    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->data) ?
                $this->data[$key] :
                value($default);
    }
	
	public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
	}

	public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
	}
    
    /**
     * @return Iterator<int|string,V>
     */
	public function getIterator()
    {
        return (new ArrayObject($this->data))->getIterator();
	}

    #endregion


    #region Apply methods
    
    /**
     * @return static<V>
     */
    public function reverse()
    {
        return new static(array_reverse($this->data));
    }

    /**
     * @return static<V>
     */
    public function chunk($length)
    {
        return new static(array_map(function($data) { return new static($data); }, array_chunk($this->data, $length)));
    }

    public function matrix($x, $y)
    {
        if($this->count() != $x * $y)
            throw new MmbException("Can't convert Arr {$this->count()} length to matrix {$x}x{$y}");

        return $this->chunk($x);
    }

    /**
     * @return static<V>
     */
    public function unique()
    {
        return new static(array_unique($this->data));
    }

    /**
     * @return static<V>
     */
    public function diff($array)
    {
        if($array instanceof Arrayable)
            $array = $array->toArray();
        return new static(array_diff($this->data, $array));
    }

    /**
     * @return static<V>
     */
    public function notNull()
    {
        return $this->filter(function($value) { return !is_null($value); });
    }

    /**
     * @return static<V>
     */
    public function filter($callback)
    {
        return new static(array_filter($this->data, $callback));
    }

    public function pluck(...$name)
    {
        if(!$name) return $this;
        if(count($name) == 1)
            $name = $name[0];

        // Pluck array
        if($name instanceof Arrayable)
            $name = $name->toArray();
        if(is_array($name))
        {
            $data = $this->data;
            foreach($data as $i => $value)
            {
                if(is_null($value))
                {
                    unset($data[$i]);
                }
                else
                {
                    $inner = [];
                    foreach($name as $nam)
                    {
                        $inner[] = $this->getInnerOf($value, $nam);
                    }
                    $data[$i] = new Arr($inner);
                }
            }
            return new Arr($data);
        }

        // Pluck single
        $data = $this->data;
        foreach($data as $i => $value)
        {
            if(is_null($value))
            {
                unset($data[$i]);
            }
            else
            {
                $data[$i] = $this->getInnerOf($value, $name);
            }
        }
        return new Arr($data);
    }

    public function pluckMap(...$name)
    {
        if(!$name) return $this;
        if(count($name) == 1)
            $name = $name[0];

        // Pluck array
        if($name instanceof Arrayable)
            $name = $name->toArray();
        if(is_array($name))
        {
            $data = $this->data;
            foreach($data as $i => $value)
            {
                if(is_null($value))
                {
                    unset($data[$i]);
                }
                else
                {
                    $inner = [];
                    foreach($name as $nam)
                    {
                        $inner[] = $this->getInnerOf($value, $nam);
                    }
                    $data[$i] = new Arr($inner);
                }
            }
            return new Map($data);
        }

        // Pluck single
        $data = $this->data;
        foreach($data as $i => $value)
        {
            if(is_null($value))
            {
                unset($data[$i]);
            }
            else
            {
                $data[$i] = $this->getInnerOf($value, $name);
            }
        }
        return new Map($data);
    }

    public function wide(...$names)
    {
        if(count($names) == 1 && is_array($names[0]))
            $names = $names[0];

        if(!$names)
        {
            $arr = new Arr();

            foreach($this as $item)
            {
                $arr->append(...arr(ATool::toArray($item)));
            }

            return $arr;
        }
        elseif(count($names) == 1)
        {
            return $this->pluck($names[0]);
        }
        else
        {
            $plucked = $this->pluck(...$names);
            $arr = new Arr();

            foreach($plucked as $items)
            {
                $arr->append(...$items);
            }

            return $arr;
        }
    }

    /**
     * @return static<V>
     */
    public function limit($length, $offset = 0)
    {
        return new static(array_slice($this->data, $offset, $length));
    }

    /**
     * @return static<V>
     */
    public function offset($offset)
    {
        return new static(array_slice($this->data, $offset));
    }

    /**
     * @return Map<Arr<V>>
     */
    public function groupBy(string $name)
    {
        // Pluck single
        $data = $this->data;
        $grouped = [];
        foreach($data as $value)
        {
            if(!is_null($value))
            {
                $item = $this->getInnerOf($value, $name);
                $grouped[$item] ??= [];
                $grouped[$item][] = $value;
            }
        }
        return (new Map($grouped))->map(fn($item) => arr($item));
    }

    private static function getInnerOf($value, $name)
    {
        if($value instanceof ArrayAccess)
        {
            return $value[$name];
        }
        elseif(is_object($value))
        {
            return $value->$name;
        }
        else
        {
            return $value[$name];
        }
    }

    #endregion


    #region Calculate methods

    public function min()
    {
        if($this->isEmpty())
            return false;

        $isset = false;
        $min = null;
        foreach($this->data as $value)
        {
            if(!$isset)
            {
                $isset = true;
                $min = $value;
            }
            elseif(Operator::isBiggerThan($min, $value))
            {
                $min = $value;
            }
        }

        return $min;
    }

    public function max()
    {
        if($this->isEmpty())
            return false;

        $isset = false;
        $min = null;
        foreach($this->data as $value)
        {
            if(!$isset)
            {
                $isset = true;
                $min = $value;
            }
            elseif(Operator::isSmallerThan($min, $value))
            {
                $min = $value;
            }
        }

        return $min;
    }

    public function sum()
    {
        return array_sum($this->data);
    }

    public function sumBig()
    {
        $sum = BigNumber::from(null);
        foreach($this->data as $value)
            $sum = $sum->add($value);

        return $sum;
    }

    /**
     * میانگین مقادیر
     * 
     * در صورت خالی بودن آرایه، فالس برگردانده می شود
     * 
     * @return int|float|false
     */
    public function avg()
    {
        if($this->isEmpty())
            return false;

        return $this->sum() / $this->count();
    }

    /**
     * میانگین مقادیر بصورت عدد بزرگ
     * 
     * در صورت خالی بودن آرایه، فالس برگردانده می شود
     * 
     * @return BigNumber|false
     */
    public function avgBig()
    {
        if($this->isEmpty())
            return false;

        return $this->sumBig()->division($this->count());
    }

    /**
     * محاسبه کردن
     * 
     * `$sum = $list->calculate(function($current, $before) { return $current + $before; }, 0);`
     * `$fx = $list->calculate(function($current, $before) { return $current * $before; }, 1);`
     * `$max = $list->calculate(function($current, $before) { return max($current, $before); }, PHP_INT_MIN);`
     * `$min = $list->calculate('min', $list[0]);`
     * 
     * @param \Closure|callable $callback
     * @param mixed $default
     * @return mixed
     */
    public function calculate($callback, $default)
    {
        $result = $default;
        foreach($this->data as $value)
        {
            $result = $callback($value, $result);
        }
        return $result;
    }
    
    #endregion


    #region Check methods
    
    public function contains($value)
    {
        foreach($this->data as $data)
        {
            if($data == $value)
                return true;
        }

        return false;
    }

    public function containsStruct($value)
    {
        foreach($this->data as $data)
        {
            if($data === $value)
                return true;
        }

        return false;
    }

    public function containsAny($values)
    {
        foreach($values as $value)
        {
            if($this->contains($value))
                return true;
        }
        
        return false;
    }

    public function containsAnyStruct($values)
    {
        foreach($values as $value)
        {
            if($this->containsStruct($value))
                return true;
        }
        
        return false;
    }

    public function containsAll($values)
    {
        foreach($values as $value)
        {
            if(!$this->contains($value))
                return false;
        }
        
        return true;
    }

    public function containsAllStruct($values)
    {
        foreach($values as $value)
        {
            if(!$this->containsStruct($value))
                return false;
        }
        
        return true;
    }

    #endregion


    #region Operation methods

    /**
     * جمع زدن مقادیر
     *
     * @param int|float|array|Arr|Map $value
     * @return static
     */
    public function add($value)
    {
        if($value instanceof Arrayable)
            $value = $value->toArray();

        $data = $this->data;
        if(is_array($value))
        {
            $count2 = count($value);
            foreach($data as $i => $val)
            {
                if($i >= $count2)
                    break;
                $data[$i] = $val + $value[$i];
            }
        }
        else
        {
            foreach($data as $i => $val)
            {
                $data[$i] = $val + $value;
            }
        }

        return new static($data);
    }

    /**
     * منها کردن مقادیر
     *
     * @param int|float|array|Arr|Map $value
     * @return static
     */
    public function substract($value)
    {
        if($value instanceof Arrayable)
            $value = $value->toArray();

        $data = $this->data;
        if(is_array($value))
        {
            $count2 = count($value);
            foreach($data as $i => $val)
            {
                if($i >= $count2)
                    break;
                $data[$i] = $val - $value[$i];
            }
        }
        else
        {
            foreach($data as $i => $val)
            {
                $data[$i] = $val - $value;
            }
        }

        return new static($data);
    }

    /**
     * ضرب مقادیر
     *
     * @param int|float|array|Arr|Map $value
     * @return static
     */
    public function multiply($value)
    {
        if($value instanceof Arrayable)
            $value = $value->toArray();

        $data = $this->data;
        if(is_array($value))
        {
            $count2 = count($value);
            foreach($data as $i => $val)
            {
                if($i >= $count2)
                    break;
                $data[$i] = $val * $value[$i];
            }
        }
        else
        {
            foreach($data as $i => $val)
            {
                $data[$i] = $val * $value;
            }
        }

        return new static($data);
    }

    /**
     * تقسیم مقادیر
     *
     * @param int|float|array|Arr|Map $value
     * @return static
     */
    public function division($value)
    {
        if($value instanceof Arrayable)
            $value = $value->toArray();

        $data = $this->data;
        if(is_array($value))
        {
            $count2 = count($value);
            foreach($data as $i => $val)
            {
                if($i >= $count2)
                    break;
                $data[$i] = $val / $value[$i];
            }
        }
        elseif($value instanceof BigNumber)
        {
            foreach($data as $i => $val)
            {
                $data[$i] = $value->add($val);
            }
        }
        else
        {
            foreach($data as $i => $val)
            {
                $data[$i] = $val / $value;
            }
        }

        return new static($data);
    }

    /**
     * جمع زدن مقادیر بصورت اعداد بزرگ
     *
     * @param int|float|string|BigNumber $value
     * @return static
     */
    public function addBig($value)
    {
        if($value instanceof Arr)
            $value = $value->toArray();

        if(!($value instanceof BigNumber))
            $value = BigNumber::from($value);

        $data = $this->data;
        foreach($data as $i => $val)
        {
            if(!($val instanceof BigNumber))
                $val = BigNumber::from($val);
            $data[$i] = $val->add($value);
        }

        return new static($data);
    }

    /**
     * منها کردن مقادیر بصورت اعداد بزرگ
     *
     * @param int|float|string|BigNumber $value
     * @return static
     */
    public function substractBig($value)
    {
        if($value instanceof Arr)
            $value = $value->toArray();

        if(!($value instanceof BigNumber))
            $value = BigNumber::from($value);

        $data = $this->data;
        foreach($data as $i => $val)
        {
            if(!($val instanceof BigNumber))
                $val = BigNumber::from($val);
            $data[$i] = $val->substract($value);
        }

        return new static($data);
    }

    /**
     * ضرب مقادیر بصورت اعداد بزرگ
     *
     * @param int|float|string|BigNumber $value
     * @return static
     */
    public function multiplyBig($value)
    {
        if($value instanceof Arr)
            $value = $value->toArray();

        if(!($value instanceof BigNumber))
            $value = BigNumber::from($value);

        $data = $this->data;
        foreach($data as $i => $val)
        {
            if(!($val instanceof BigNumber))
                $val = BigNumber::from($val);
            $data[$i] = $val->multiply($value);
        }

        return new static($data);
    }

    /**
     * تقسیم مقادیر بصورت اعداد بزرگ
     *
     * @param int|float|string|BigNumber $value
     * @return static
     */
    public function divisionBig($value)
    {
        if($value instanceof Arr)
            $value = $value->toArray();

        if(!($value instanceof BigNumber))
            $value = BigNumber::from($value);

        $data = $this->data;
        foreach($data as $i => $val)
        {
            if(!($val instanceof BigNumber))
                $val = BigNumber::from($val);
            $data[$i] = $val->division($value);
        }

        return new static($data);
    }

    #endregion


    #region Convert

    public function toList()
    {
        return new Arr($this);
    }

    public function toMap()
    {
        return new Map($this);
    }

    public function toMapBy(string|Closure $keyBy)
    {
        $map = new Map;
        if(is_string($keyBy))
        {
            foreach($this as $item)
            {
                $key = static::getInnerOf($item, $keyBy);
                $map->set($key, $item);
            }
        }
        else
        {
            foreach($this as $key => $item)
            {
                $key = $keyBy($item, $key);
                $map->set($key, $item);
            }
        }
        return $map;
    }

    #endregion


    #region Php convert

    /**
     * @return array<V>
     */
    public function toArray()
    {
        return $this->data;
    }

    public function toBoolean()
    {
        return $this->isNotEmpty();
    }

    public function isEmpty()
    {
        return !count($this->data);
    }

    public function isNotEmpty()
    {
        return !$this->isEmpty();
    }

    public function bool()
    {
        return $this->toBoolean();
    }

    #endregion


    #region Sorting


    /**
     * @return static<V>
     */
    public function sortBy(...$keys)
    {
        if(!$keys) return $this;
        if(count($keys) == 1)
            $keys = $keys[0];
            
        $result = [];
        foreach($this->pluckMap($keys)->sort() as $key => $_)
        {
            $result[$key] = $this->data[$key];
        }
        return new static($result);
    }
    

    /**
     * @return static<V>
     */
    public function sortDescBy(...$keys)
    {
        if(!$keys) return $this;
        if(count($keys) == 1)
            $keys = $keys[0];
            
        $result = [];
        foreach($this->pluckMap($keys)->sortDesc() as $key => $_)
        {
            $result[$key] = $this->data[$key];
        }
        return new static($result);
    }
    
    // public function sortBy($key)
    // {
    //     $sortedArray = $this->data;
    //     $count = $this->count() - 1;
    //     for($i = 0; $i < $count; $i++)
    //     {
    //         for($j = 0; $j < $count - $i; $j++)
    //         {
    //             if($sortedArray[$j][$key] > $sortedArray[$j + 1][$key])
    //             {
    //                 $temp = $sortedArray[$j + 1];
    //                 $sortedArray[$j + 1] = $sortedArray[$j];
    //                 $sortedArray[$j] = $temp;
    //             }
    //         }
    //     }
    //     return new static($sortedArray);
    // }

    // public function sortDescBy($key)
    // {
    //     $sortedArray = $this->data;
    //     $count = $this->count() - 1;
    //     for($i = 0; $i < $count; $i++)
    //     {
    //         for($j = 0; $j < $count - $i; $j++)
    //         {
    //             if($sortedArray[$j][$key] < $sortedArray[$j + 1][$key])
    //             {
    //                 $temp = $sortedArray[$j + 1];
    //                 $sortedArray[$j + 1] = $sortedArray[$j];
    //                 $sortedArray[$j] = $temp;
    //             }
    //         }
    //     }
    //     return new static($sortedArray);
    // }


    #endregion


    #region Where

    /**
     * @param mixed $value
     * @param string $findBy
     * @return V
     */
    public function find($value, $findBy = 'id')
    {
        foreach($this->data as $item)
        {
            if(static::getInnerOf($item, $findBy) == $value)
            {
                return $item;
            }
        }

        return false;
    }

    /**
     * @return static<V>
     */
    public function whereBy(callable $map, $operator, $value = null)
    {
        if(func_num_args() == 2)
        {
            $value = $operator;
            $operator = '=';
        }

        switch(strtolower($operator))
        {
            case '=':
                $filter = function($data) use($map, $value)
                {
                    return !is_null($data) && Operator::isEqualsTo($map($data), $value);
                };
                break;
            case '<>':
            case '!=':
                $filter = function($data) use($map, $value)
                {
                    return !is_null($data) && Operator::isNotEqualsTo($map($data), $value);
                };
                break;
            case 'is':
                if($value === null)
                {
                    $filter = function($data) use($map)
                    {
                        return is_null($data) || is_null($map($data));
                    };
                }
                else
                {
                    $filter = function($data) use($map, $value)
                    {
                        return !is_null($data) && $map($data) === $value;
                    };
                }
                break;
            case 'not is':
                if($value === null)
                {
                    $filter = function($data) use($map)
                    {
                        return !is_null($data) && !is_null($map($data));
                    };
                }
                else
                {
                    $filter = function($data) use($map, $value)
                    {
                        return !is_null($data) && $map($data) !== $value;
                    };
                }
                break;
            case '>':
                $filter = function($data) use($map, $value)
                {
                    return !is_null($data) && Operator::isBiggerThan($map($data), $value);
                };
                break;
            case '>=':
                $filter = function($data) use($map, $value)
                {
                    return !is_null($data) && Operator::isEqualsOrBiggerThan($map($data), $value);
                };
                break;
            case '<':
                $filter = function($data) use($map, $value)
                {
                    return !is_null($data) && Operator::isSmallerThan($map($data), $value);
                };
                break;
            case '<=':
                $filter = function($data) use($map, $value)
                {
                    return !is_null($data) && Operator::isEqualsOrSmallerThan($map($data), $value);
                };
                break;
            case 'in':
                if($value instanceof Arrayable)
                    $value = $value->toArray();

                $filter = function($data) use($map, $value)
                {
                    return !is_null($data) && in_array($map($data), $value);
                };
                break;
            case 'not in':
            case 'notin':
                if($value instanceof Arrayable)
                    $value = $value->toArray();

                $filter = function($data) use($map, $value)
                {
                    return !is_null($data) && !in_array($map($data), $value);
                };
                break;
            case 'like':
                $value = preg_quote($value);
                $value = str_replace(['_', '%'], ['.', '.*'], $value);
                $filter = function($data) use($map, $value)
                {
                    return !is_null($data) && preg_match('/^'.$value.'$/u', $map($data));
                };
                break;
            case 'regexp':
                $filter = function($data) use($map, $value)
                {
                    return !is_null($data) && preg_match('/'.$value.'/u', $map($data));
                };
                break;
            case 'regex':
                $filter = function($data) use($map, $value)
                {
                    return !is_null($data) && preg_match($value, $map($data));
                };
                break;
            default:
                throw new MmbException("Operator '{$operator}' is not supported");
        }

        return $this->filter($filter);
    }

    /**
     * @return static<V>
     */
    public function where($key, $operator, $value = null)
    {
        if(func_num_args() == 2)
        {
            $value = $operator;
            $operator = '=';
        }

        switch(strtolower($operator))
        {
            case '=':
                $filter = function($data) use($key, $value)
                {
                    return !is_null($data) && Operator::isEqualsTo(static::getInnerOf($data, $key), $value);
                };
                break;
            case '<>':
            case '!=':
                $filter = function($data) use($key, $value)
                {
                    return !is_null($data) && Operator::isNotEqualsTo(static::getInnerOf($data, $key), $value);
                };
                break;
            case 'is':
                if($value === null)
                {
                    $filter = function($data) use($key)
                    {
                        return is_null($data) || is_null(static::getInnerOf($data, $key));
                    };
                }
                else
                {
                    $filter = function($data) use($key, $value)
                    {
                        return !is_null($data) && static::getInnerOf($data, $key) === $value;
                    };
                }
                break;
            case 'not is':
                if($value === null)
                {
                    $filter = function($data) use($key)
                    {
                        return !is_null($data) && !is_null(static::getInnerOf($data, $key));
                    };
                }
                else
                {
                    $filter = function($data) use($key, $value)
                    {
                        return !is_null($data) && static::getInnerOf($data, $key) !== $value;
                    };
                }
                break;
            case '>':
                $filter = function($data) use($key, $value)
                {
                    return !is_null($data) && Operator::isBiggerThan(static::getInnerOf($data, $key), $value);
                };
                break;
            case '>=':
                $filter = function($data) use($key, $value)
                {
                    return !is_null($data) && Operator::isEqualsOrBiggerThan(static::getInnerOf($data, $key), $value);
                };
                break;
            case '<':
                $filter = function($data) use($key, $value)
                {
                    return !is_null($data) && Operator::isSmallerThan(static::getInnerOf($data, $key), $value);
                };
                break;
            case '<=':
                $filter = function($data) use($key, $value)
                {
                    return !is_null($data) && Operator::isEqualsOrSmallerThan(static::getInnerOf($data, $key), $value);
                };
                break;
            case 'in':
                if($value instanceof Arrayable)
                    $value = $value->toArray();

                $filter = function($data) use($key, $value)
                {
                    return !is_null($data) && in_array(static::getInnerOf($data, $key), $value);
                };
                break;
            case 'not in':
            case 'notin':
                if($value instanceof Arrayable)
                    $value = $value->toArray();

                $filter = function($data) use($key, $value)
                {
                    return !is_null($data) && !in_array(static::getInnerOf($data, $key), $value);
                };
                break;
            case 'like':
                $value = preg_quote($value);
                $value = str_replace(['_', '%'], ['.', '.*'], $value);
                $filter = function($data) use($key, $value)
                {
                    return !is_null($data) && preg_match('/^'.$value.'$/u', static::getInnerOf($data, $key));
                };
                break;
            case 'regexp':
                $filter = function($data) use($key, $value)
                {
                    return !is_null($data) && preg_match('/'.$value.'/u', static::getInnerOf($data, $key));
                };
                break;
            case 'regex':
                $filter = function($data) use($key, $value)
                {
                    return !is_null($data) && preg_match($value, static::getInnerOf($data, $key));
                };
                break;
            default:
                throw new MmbException("Operator '{$operator}' is not supported");
        }

        return $this->filter($filter);
    }

    /**
     * @return static<V>
     */
    public function whereCol($key, $operator, $key2 = null)
    {
        if(func_num_args() == 2)
        {
            $key2 = $operator;
            $operator = '=';
        }

        switch(strtolower($operator))
        {
            case '=':
                $filter = function($data) use($key, $key2)
                {
                    return !is_null($data) && Operator::isEqualsTo($data[$key], $data[$key2]);
                };
                break;
            case '<>':
            case '!=':
                $filter = function($data) use($key, $key2)
                {
                    return !is_null($data) && Operator::isNotEqualsTo($data[$key], $data[$key2]);
                };
                break;
            case 'is':
                $filter = function($data) use($key, $key2)
                {
                    return !is_null($data) && $data[$key] === $data[$key2];
                };
                break;
            case 'not is':
                $filter = function($data) use($key, $key2)
                {
                    return !is_null($data) && $data[$key] !== $data[$key2];
                };
                break;
            case '>':
                $filter = function($data) use($key, $key2)
                {
                    return !is_null($data) && Operator::isBiggerThan($data[$key], $data[$key2]);
                };
                break;
            case '>=':
                $filter = function($data) use($key, $key2)
                {
                    return !is_null($data) && Operator::isEqualsOrBiggerThan($data[$key], $data[$key2]);
                };
                break;
            case '<':
                $filter = function($data) use($key, $key2)
                {
                    return !is_null($data) && Operator::isSmallerThan($data[$key], $data[$key2]);
                };
                break;
            case '<=':
                $filter = function($data) use($key, $key2)
                {
                    return !is_null($data) && Operator::isEqualsOrSmallerThan($data[$key], $data[$key2]);
                };
                break;
            default:
                throw new MmbException("Operator '{$operator}' is not supported");
        }

        return $this->filter($filter);
    }

    /**
     * @return V|false
     */
    public function whereFirstBy(callable $map, $operator, $value = null)
    {
        if(func_num_args() == 2)
        {
            $value = $operator;
            $operator = '=';
        }

        switch(strtolower($operator))
        {
            case '=':
                foreach($this->data as $data)
                {
                    if(!is_null($data) && Operator::isEqualsTo($map($data), $value))
                    {
                        return $data;
                    }
                }
                break;
            case '<>':
            case '!=':
                foreach($this->data as $data)
                {
                    if(!is_null($data) && Operator::isNotEqualsTo($map($data), $value))
                    {
                        return $data;
                    }
                }
                break;
            case 'is':
                if($value === null)
                {
                    foreach($this->data as $data)
                    {
                        if(is_null($data) || is_null($map($data)))
                        {
                            return $data;
                        }
                    }
                }
                else
                {
                    foreach($this->data as $data)
                    {
                        if(!is_null($data) && $map($data) === $value)
                        {
                            return $data;
                        }
                    }
                }
                break;
            case 'not is':
                if($value === null)
                {
                    foreach($this->data as $data)
                    {
                        if(!is_null($data) && !is_null($map($data)))
                        {
                            return $data;
                        }
                    }
                }
                else
                {
                    foreach($this->data as $data)
                    {
                        if(!is_null($data) && $map($data) !== $value)
                        {
                            return $data;
                        }
                    }
                }
                break;
            case '>':
                foreach($this->data as $data)
                {
                    if(!is_null($data) && Operator::isBiggerThan($map($data), $value))
                    {
                        return $data;
                    }
                }
                break;
            case '>=':
                foreach($this->data as $data)
                {
                    if(!is_null($data) && Operator::isEqualsOrBiggerThan($map($data), $value))
                    {
                        return $data;
                    }
                }
                break;
            case '<':
                foreach($this->data as $data)
                {
                    if(!is_null($data) && Operator::isSmallerThan($map($data), $value))
                    {
                        return $data;
                    }
                }
                break;
            case '<=':
                foreach($this->data as $data)
                {
                    if(!is_null($data) && Operator::isEqualsOrSmallerThan($map($data), $value))
                    {
                        return $data;
                    }
                }
                break;
            case 'in':
                if($value instanceof Arrayable)
                    $value = $value->toArray();

                foreach($this->data as $data)
                {
                    if(!is_null($data) && in_array($map($data), $value))
                    {
                        return $data;
                    }
                }
                break;
            case 'not in':
            case 'notin':
                if($value instanceof Arrayable)
                    $value = $value->toArray();

                foreach($this->data as $data)
                {
                    if(!is_null($data) && !in_array($map($data), $value))
                    {
                        return $data;
                    }
                }
                break;
            case 'like':
                $value = preg_quote($value);
                $value = str_replace(['_', '%'], ['.', '.*'], $value);
                foreach($this->data as $data)
                {
                    if(!is_null($data) && preg_match('/^'.$value.'$/u', $map($data)))
                    {
                        return $data;
                    }
                }
                break;
            case 'regexp':
                foreach($this->data as $data)
                {
                    if(!is_null($data) && preg_match('/'.$value.'/u', $map($data)))
                    {
                        return $data;
                    }
                }
                break;
            case 'regex':
                foreach($this->data as $data)
                {
                    if(!is_null($data) && preg_match($value, $map($data)))
                    {
                        return $data;
                    }
                }
                break;
            default:
                throw new MmbException("Operator '{$operator}' is not supported");
        }

        return false;
    }

    /**
     * @return V|false
     */
    public function whereFirst($key, $operator, $value = null)
    {
        $args = func_get_args();
        $args[0] = fn($data) => static::getInnerOf($data, $key);
        
        return $this->whereFirstBy(...$args);
    }

    /**
     * @return static<V>
     */
    public function whereIn($key, $array)
    {
        return $this->where($key, 'in', $array);
    }

    /**
     * @return V|false
     */
    public function whereFirstIn($key, $array)
    {
        return $this->whereFirst($key, 'in', $array);
    }

    /**
     * @return static<V>
     */
    public function whereNotIn($key, $array)
    {
        return $this->where($key, 'not in', $array);
    }

    /**
     * @return V|false
     */
    public function whereFirstNotIn($key, $array)
    {
        return $this->where($key, 'in', $array);
    }

    /**
     * مقدار هایی که تعداد دلخواه شما را دارند برگردانده می شوند
     *
     * @param string $key
     * @param string $operator
     * @param integer $count
     * @return static<V>
     */
    public function whereHas($key, $operator = '>=', $count = 1)
    {
        return $this->whereBy(function($data) use($key)
        {
            $value = $this->getInnerOf($data, $key);
            return is_countable($value) ? count($value) : 0;
        }, $operator, $count);
    }

    #endregion


    #region Random

    /**
     * یک مقدار رندوم
     *
     * @return ?V
     */
    public function random()
    {
        return $this->data ? $this->data[array_rand($this->data)] : null;
    }

    /**
     * چند مقدار رندوم
     *
     * @param int $num
     * @return static<V>
     */
    public function randoms($num)
    {
        $rands = [];
        foreach(array_rand($this->data, $num) as $key)
        {
            $rands[$key] = $this->data[$key];
        }
        return new static($rands);
    }

    /**
     * کلید های بهم ریخته بر می گرداند
     *
     * @return Arr<V>
     */
    public function shuffleKeys()
    {
        $data = array_keys($this->data);
        shuffle($data);

        return new Arr($data);
    }

    #endregion


    #region Only

    /**
     * @param mixed ...$keys
     * @return static<V>
     */
    public function only(...$keys)
    {
        $data = [];
        foreach($keys as $key)
        {
            $data[$key] = $this->data[$key];
        }

        return new static($data);
    }

    /**
     * @param mixed ...$keys
     * @return static<V>
     */
    public function forgot(...$keys)
    {
        $data = $this->data;
        foreach($keys as $key)
        {
            unset($data[$key]);
        }

        return new static($data);
    }

    #endregion

}
