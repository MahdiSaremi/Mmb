<?php

namespace Mmb\Mapping; #auto

use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Mmb\Tools\ATool;
use Mmb\Tools\Type;

class Map implements Countable, ArrayAccess, IteratorAggregate, JsonSerializable
{

    private $data;
    
    /**
     * @param array $array
     */
    public function __construct(array $array = [])
    {
        $this->data = $array;
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
        unset($this->data[$offset]);
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

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function replace(array $array)
    {
        $this->data = array_replace($this->data, $array);
    }

    public function remove($key)
    {
        unset($this->data[$key]);
    }

    public function pop()
    {
        return array_pop($this->data);
    }

    public function join($separator)
    {
        return join($this->data, $separator);
    }

    public function joinAssoc($separator, $keyValSeparator, $startWithValue = false)
    {
        $first = true;
        $res = "";

        foreach($this->data as $key => $value)
        {
            if ($first)
                $first = false;
            else
                $res .= $separator;

            $res .= $startWithValue ? "$value$keyValSeparator$key" : "$key$keyValSeparator$value";
        }

        return $res;
    }

    public function reverse()
    {
        $this->data = array_reverse($this->data, true);
    }

    public function unique()
    {
        $this->data = array_unique($this->data);
    }

    public function keyOf($value)
    {
        return array_search($value, $this->data);
    }

    public function filter($callback)
    {
        foreach($this->data as $key => $value)
        {
            if (!$callback($key, $value))
                unset($this->data[$key]);
        }
    }

    public function map($callback)
    {
        foreach($this->data as $key => $value)
        {
            $this->data[$key] = $callback($key, $value);
        }
    }

    public function first()
    {
        return $this->data ? $this->data[array_key_first($this->data)] : null;
    }
    
    public function last()
    {
        return $this->data ? end($this->data) : null;
    }

    public function firstKey()
    {
        return array_key_first($this->data);
    }

    public function lastKey()
    {
        return array_key_last($this->data);
    }

    public function toArray()
    {
        return $this->data;
    }

}
