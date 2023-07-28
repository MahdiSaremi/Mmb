<?php

namespace Mmb\Mapping; #auto

use ArrayAccess;
use ArrayObject;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Mmb\Big\BigNumber;
use Mmb\Exceptions\MmbException;
use Mmb\Tools\ATool;
use Mmb\Tools\Operator;

/**
 * @template V
 * @extends ArrayableObject<V>
 */
class Map extends ArrayableObject implements JsonSerializable
{
    
    /**
     * @template V
     * @param array<V>|Arrayable<V> $array
     */
    public function __construct(array|Arrayable $array = [])
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        $this->data = $array;
    }


    // Interface functions

	public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
	}


    // Serialize & Unserialize

    public function jsonSerialize()
    {
        return $this->data;
    }

    public function __serialize()
    {
        return $this->data;
    }

    public function __unserialize(array $data)
    {
        $this->data = $data;
    }


    // Tools

    /**
     * @return static<V>
     */
    public function merge(...$maps)
    {
        $data = $this->data;
        foreach($maps as $i => $map)
        {
            if($map instanceof Map || $map instanceof Arr)
                $maps[$i] = $map->toArray();
        }
        $data = array_replace($data, ...$maps);
        return new static($data);
    }

    /**
     * @return static<V>
     */
    public function leftMerge(...$maps)
    {
        $data = $this->data;
        foreach($maps as $map)
        {
            if($map instanceof Map || $map instanceof Arr)
                $map = $map->toArray();
            $data += $map;
        }
        return new static($data);
    }

    /**
     * @return static<V>
     */
    public function remove($index)
    {
        $data = $this->data;
        unset($data[$index]);
        return new static($data);
    }

    /**
     * @return static<V>
     */
    public function set($key, $value)
    {
        $data = $this->data;
        $data[$key] = $value;
        return new static($data);
    }

    /**
     * @return static<V>
     */
    public function move($fromKey, $toKey)
    {
        $data = $this->data;
        $temp = $data[$fromKey];
        $data[$fromKey] = $data[$toKey];
        $data[$toKey] = $temp;
        return new static($data);
    }

    public function implodeValues($separator)
    {
        return implode($this->data, $separator);
    }

    public function implodeKeys($separator)
    {
        return implode(array_keys($this->data), $separator);
    }

    public function implode($separator, $separator2 = ": ")
    {
        // return implode($this->map(), $separator);
    }

    /**
     * @return static<V>
     */
    public function filter($callback)
    {
        return new static(array_filter($this->data, $callback, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * @return static<V>
     */
    public function map($callback)
    {
        return new static(array_map($callback, $this->data));
    }
    /**
     * @return static<V>
     */
    public function each($callback)
    {
        $result = [];
        foreach($this->data as $key => $value)
        {
            $callback($key, $value);
            $result[$key] = $value;
        }
        return new static($result);
    }

    /**
     * @return static<V>
     */
    public function mapKey($callback)
    {
        $res = [];
        foreach($this->data as $key => $val)
        {
            $res[$callback($key)] = $val;
        }
        return new static($res);
    }

    /**
     * @return Arr<V>
     */
    public function keys()
    {
        return new Arr(array_keys($this->data));
    }
    /**
     * @return Arr<V>
     */
    public function values()
    {
        return new Arr(array_values($this->data));
    }

    /**
     * @return static<V>
     */
    public function sort()
    {
        $data = $this->data;
        asort($data);
        return new static($data);
    }

    /**
     * @return static<V>
     */
    public function sortDesc()
    {
        $data = $this->data;
        arsort($data);
        return new static($data);
    }




    public function keyOf($value)
    {
        return array_search($value, $this->data);
    }
    /**
     * @return V
     */
    public function valueOf($key)
    {
        return $this->data[$key] ?? false;
    }

    public function divide()
    {
        return new Arr([ $this->keys(), $this->values() ]);
    }

    


    /**
     * @return V
     */
    public function first()
    {
        return $this->data ? $this->data[array_key_first($this->data)] : null;
    }
    
    /**
     * @return V
     */
    public function last()
    {
        return $this->data ? end($this->data) : null;
    }

    public function __toString()
    {
        if($this->isEmpty())
            return "[]";
        
        $str = "";
        foreach($this->data as $key => $val)
        {
            if($str) $str .= ", ";
            $str .= "{$key} => {$val}";
        }

        return '[' . $str . ']';
    }

}
