<?php

namespace Mmb\Mapping; #auto

use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Mmb\Tools\ATool;
use Mmb\Tools\Type;

class Arr implements Countable, ArrayAccess, IteratorAggregate, JsonSerializable
{

    private $data;
    
    /**
     * @param array $array
     */
    public function __construct(array $array = [])
    {
        $this->data = array_values($array);
    }


    // Interface functions

    public function count()
    {
        return count($this->data);
    }

	public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
	}
	
	public function offsetGet($offset)
    {
        return $this->data[$offset];
	}
	
	public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
	}
	
	public function offsetUnset($offset)
    {
        ATool::remove($this->data, $offset);
	}
    
	public function getIterator()
    {
        return $this->data;
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

    public function push(...$value)
    {
        return array_push($this->data, ...$value);
    }

    public function remove($index)
    {
        ATool::remove($this->data, $index);
    }

    public function insert($index, $value, ...$values)
    {
        ATool::insert($this->data, $index, $value);
        if($values)
            ATool::insertMulti($this->data, $index + 1, $values);
    }

    public function pop()
    {
        return array_pop($this->data);
    }

    public function move($fromIndex, $toIndex)
    {
        ATool::move($this->data, $fromIndex, $toIndex);
    }

    public function join($separator)
    {
        return join($this->data, $separator);
    }

    public function reverse()
    {
        $this->data = array_reverse($this->data);
    }

    public function chunk($length)
    {
        $this->data = array_chunk($this->data, $length);
    }

    public function unique()
    {
        $this->data = array_values(array_unique($this->data));
    }

    public function indexOf($value)
    {
        $index = array_search($value, $this->data);

        return $index === false ? -1 : $index;
    }

    public function filter($callback)
    {
        $this->data = array_filter($this->data, $callback);
    }

    public function map($callback)
    {
        $this->data = array_map($callback, $this->data);
    }

    public function sum()
    {
        return array_sum($this->data);
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

    public function first()
    {
        return $this->data ? $this->data[0] : null;
    }
    
    public function last()
    {
        return $this->data ? end($this->data) : null;
    }

    public function toArray()
    {
        return $this->data;
    }

}
