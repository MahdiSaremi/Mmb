<?php
#auto-name
namespace Mmb\Pay;
use ArrayAccess;
use Countable;
use IteratorAggregate;

class PayInfo implements ArrayAccess, Countable, IteratorAggregate
{
    
    /**
     * @var array
     */
    public $data;

    /**
     * ورودی ها
     * 
     * @var array
     */
    public $args;

    /**
     * کد پیگیری پرداخت
     * 
     * پر شدن این مقدار به درگاه بستگی دارد
     * 
     * @var string
     */
    public $ref;

    public function __construct($data)
    {
        $this->data = $data;
        $this->args = $data['args'] ?: [];
    }

	public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->args);
	}
	
	public function offsetGet($offset)
    {
        return $this->args[$offset];
	}
	
	public function offsetSet($offset, $value)
    {
        $this->args[$offset] = $value;
	}
	
	public function offsetUnset($offset)
    {
        unset($this->args[$offset]);
	}
	
	public function count()
    {
        return count($this->args);
	}
	
	public function getIterator()
    {
        return $this->args;
	}

    public function __get($name)
    {
        return $this->data[$name];
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

}
