<?php

namespace Mmb\Mapping; #auto

use JsonSerializable;

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
     * @return $this
     */
    public function merge(...$maps)
    {
        foreach($maps as $i => $map)
        {
            if($map instanceof Arrayable)
                $maps[$i] = $map->toArray();
        }
        $this->data = array_replace($this->data, ...$maps);
        return $this;
    }

    /**
     * @return $this
     */
    public function leftMerge(...$maps)
    {
        foreach($maps as $map)
        {
            if($map instanceof Arrayable)
                $map = $map->toArray();
            $this->data += $map;
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function remove($index)
    {
        unset($this->data[$index]);
        return $this;
    }

    /**
     * حذف یک مقدار
     *
     * @param mixed $value
     * @return $this
     */
    public function removeValue($value, bool $strict = false)
    {
        if(($key = array_search($value, $this->data, $strict)) !== false)
        {
            unset($this->data[$key]);
        }
        
        return $this;
    }

    /**
     * @return $this
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @return $this
     */
    public function move($fromKey, $toKey)
    {
        $temp = $this->data[$fromKey];
        $this->data[$fromKey] = $this->data[$toKey];
        $this->data[$toKey] = $temp;
        return $this;
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
     * @return static<V>
     */
    public function mapKeyByItem($callback)
    {
        $res = [];
        foreach($this->data as $key => $val)
        {
            $res[$callback($val, $key)] = $val;
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
     * @return int|string|null
     */
    public function firstKey()
    {
        return $this->data ? array_key_first($this->data) : null;
    }
    
    /**
     * @return V
     */
    public function last()
    {
        return $this->data ? end($this->data) : null;
    }

    /**
     * @return int|string|null
     */
    public function lastKey()
    {
        return $this->data ? array_key_last($this->data) : null;
    }

    /**
     * بهم ریختن مقادیر مپ
     * 
     * کلید ها بهم نمیریزند! برای بهم ریختن کلید ها، از روش زیر استفاده کنید.
     * `$shuffleValues = $map->values()->shuffle();`
     *
     * @return static<V>
     */
    public function shuffle()
    {
        $keys = $this->shuffleKeys();

        $result = [];
        foreach($keys as $key)
        {
            $result[$key] = $this->data[$key];
        }

        return new static($result);
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
