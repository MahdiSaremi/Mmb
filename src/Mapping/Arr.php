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
     * @return static<V>
     */
    public function append(...$value)
    {
        return new static(array_merge($this->data, $value));
    }

    /**
     * @return static<V>
     */
    public function remove($index)
    {
        $data = $this->data;
        ATool::remove($data, $index);
        return new static($data);
    }

    /**
     * @return static<V>
     */
    public function insert($index, $value, ...$values)
    {
        $data = $this->data;
        ATool::insert($data, $index, $value);
        if($values)
            ATool::insertMulti($data, $index + 1, $values);

        return new static($data);
    }

    /**
     * @return static<V>
     */
    public function move($fromIndex, $toIndex)
    {
        $data = $this->data;
        ATool::move($data, $fromIndex, $toIndex);
        return new static($data);
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
     * @return V
     */
    public function first()
    {
        return $this->data ? $this->data[0] : null;
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
        
        // return "[\"" . join("\", \"", array_map('addslashes', $this->data)) . "\"]";
        return "[" . join(", ", $this->data) . "]";
    }

}
