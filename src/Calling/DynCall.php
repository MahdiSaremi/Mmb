<?php
#auto-name
namespace Mmb\Calling;

use BadMethodCallException;
use Mmb\Db\Relation\Relation;

trait DynCall
{

    protected $_invokes_var = [];
    
    public final function &__get($name)
    {
        if(array_key_exists($name, $this->_invokes_var))
        {
            $result = $this->_invokes_var[$name];
        }
        elseif(method_exists($this, $name))
        {
            $result = Caller::invoke($this, $name);
            if($result instanceof Relation)
            {
                $result = $result->getRelationValue();
            }
            $this->_invokes_var[$name] = $result;
        }
        elseif(method_exists($this, "get$name"))
        {
            $result = Caller::invoke($this, "get$name");
        }
        else
        {
            $result = $this->__get_proto($name);
        }

        if($result instanceof DynProperty)
        {
            $result = $result->get();
        }

        return $result;
    }
    public function __get_proto($name)
    {
        return null;
    }

    public final function __set($name, $value)
    {
        if(method_exists($this, "set$name"))
        {
            Caller::invoke($this, "set$name", [ $value ]);
            return true;
        }
        elseif(array_key_exists($name, $this->_invokes_var))
        {
            $result = $this->_invokes_var[$name];
        }
        elseif(method_exists($this, $name))
        {
            $result = Caller::invoke($this, $name);
            $this->_invokes_var[$name] = $result;
        }
        elseif($this->__set_proto($name, $value))
        {
            return;
        }
        else
        {
            $this->_invokes_var[$name] = $value;
            return;
        }

        if($result instanceof DynProperty)
        {
            $result->set($value);
        }
        else
        {
            $this->_invokes_var[$name] = $value;
            return;
        }
    }
    public function __set_proto($name, $value)
    {
        return false;
    }

    /**
     * مقدار داینامیک ذخیره شده را فراموش می کند
     *
     * @param string $name
     * @param string ...$names
     * @return void
     */
    public function dynForgot(string $name, string ...$names)
    {
        foreach(func_get_args() as $name)
        {
            if(array_key_exists($name, $this->_invokes_var))
            {
                unset($this->_invokes_var[$name]);
            }
        }
    }

    /**
     * تمامی مقادیر داینامیک ذخیره شده را پاک می کند
     *
     * @return void
     */
    public function dynClear()
    {
        $this->_invokes_var = [];
    }

    public function __call($name, $args)
    {
        throw new BadMethodCallException("Method '$name' is not exists in class " . static::class);
    }
    
    public static function __callStatic($name, $args)
    {
        throw new BadMethodCallException("Method static '$name' is not exists in class " . static::class);
    }

}
