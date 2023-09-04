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
use Mmb\Tools\Type;
use Traversable;

/**
 * @template V
 * @extends ArrayableObject<V>
 */
class Arr extends ArrayableObject implements JsonSerializable
{

    /**
     * @param array|Arrayable $array
     */
    public function __construct(array|Arrayable $array = [])
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        $this->data = array_values($array);
    }


    // Interface functions

	public function offsetUnset($offset)
    {
        ATool::remove($this->data, $offset);
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
        $this->data = array_values($data);
    }


    // Tools

    /**
     * @return $this
     */
    public function append(...$value)
    {
        array_push($this->data, ...$value);
        return $this;
    }

    /**
     * @return $this
     */
    public function remove($index)
    {
        ATool::remove($this->data, $index);
        return $this;
    }

    /**
     * @return $this
     */
    public function removeValue($value, bool $strict = false)
    {
        ATool::remove2($this->data, $value, $strict);
        return $this;
    }

    /**
     * @return $this
     */
    public function insert($index, $value, ...$values)
    {
        ATool::insert($this->data, $index, $value);
        if($values)
            ATool::insertMulti($this->data, $index + 1, $values);

        return $this;
    }

    /**
     * @return $this
     */
    public function move($fromIndex, $toIndex)
    {
        ATool::move($this->data, $fromIndex, $toIndex);
        return $this;
    }

    /**
     * @return $this
     */
    public function merge(...$arrays)
    {
        foreach($arrays as $i => $array)
        {
            if($array instanceof Arrayable)
                $arrays[$i] = $array->toArray();
        }

        $this->data = array_merge($this->data, ...$arrays);
        return $this;
    }

    public function implode($separator)
    {
        return implode($separator, $this->data);
    }

    /**
     * @return static<V>
     */
    public function filter($callback)
    {
        return new static(array_filter($this->data, $callback));
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
    public function walk($callback)
    {
        $data = $this->data;
        array_walk($data, $callback);
        return new static($data);
    }
    /**
     * @return static<V>
     */
    public function each($callback)
    {
        $data = $this->data;
        foreach($data as $index => $value)
        {
            $callback($data[$index]);
        }
        return new static($data);
    }

    /**
     * @return static<int|string>
     */
    public function indexs()
    {
        return new static(array_keys($this->data));
    }

    /**
     * @return static<V>
     */
    public function sort()
    {
        $data = $this->data;
        sort($data);
        return new static($data);
    }

    /**
     * @return static<V>
     */
    public function sortDesc()
    {
        $data = $this->data;
        rsort($data);
        return new static($data);
    }

    /**
     * @return Map<V>
     */
    public function assocBy($key)
    {
        $result = [];
        foreach($this->pluckMap($key) as $index => $value)
        {
            $result[$value] = $this->data[$index];
        }
        return new Map($result);
    }

    /**
     * داده ها را بر اساس لیست داده شده مرتب می کند
     * 
     * `$arr->sortWith('id', [ 10, 4, 6 ])`
     * 
     * نتیجه:
     * `[ ['id' => 10], ['id' => 4], ['id' => 6] ]`
     *
     * @param string $key
     * @param array|Arrayable $values
     * @return static<V>
     */
    public function sortWith(string $key, array|Traversable|Arrayable $values)
    {
        if($values instanceof Arrayable)
        {
            $values = $values->toArray();
        }

        $keyOn = $this->pluck($key);
        $newArray = [];

        foreach($values as $value)
        {
            $index = $keyOn->indexOf($value);
            $newArray[] = $index === false ? null : $this[$index];
        }

        return new static($newArray);
    }



    public function indexOf($value)
    {
        $index = array_search($value, $this->data);

        return $index === false ? -1 : $index;
    }

    public function divide()
    {
        return new static([ $this->indexs(), $this ]);
    }
 
    


    /**
     * @return ?V
     */
    public function first()
    {
        return $this->data ? $this->data[0] : null;
    }

    /**
     * @return ?int
     */
    public function firstKey()
    {
        return $this->data ? 0 : null;
    }
    
    /**
     * @return ?V
     */
    public function last()
    {
        return $this->data ? end($this->data) : null;
    }

    /**
     * @return ?int
     */
    public function lastKey()
    {
        return $this->data ? $this->count() - 1 : null;
    }

    /**
     * بهم ریختن مقادیر آرایه
     *
     * @return static<V>
     */
    public function shuffle()
    {
        $data = $this->data;
        shuffle($data);
        
        return new static($data);
    }

    public function __toString()
    {
        if($this->isEmpty())
            return "[]";
        
        // return "[\"" . join("\", \"", array_map('addslashes', $this->data)) . "\"]";
        return "[" . join(", ", $this->data) . "]";
    }

}
