<?php

namespace Mmb\Controller; #auto

use Mmb\Controller\StepHandler\Handlable;
use Mmb\Controller\StepHandler\MenuHandler;
use Mmb\Tools\ATool;

class Menu implements Handlable
{

    /** @var MenuHandler */
    private $handler;

    public function __construct()
    {
        $this->handler = new MenuHandler;
    }
    
	public function getHandler()
    {
        return $this->handler;
	}

    /**
     * ساخت منوی جدید
     * @param array|null $keys
     * @return static
     */
    public static function new(array $keys = null)
    {
        $object = new static;

        if($keys !== null)
        {
            $object->keys($keys);
        }

        return $object;
    }

    public function keys(array $keys)
    {
        $this->handler->keys = $keys;
    }

    public function getKey()
    {
        return $this->handler->getKey();
    }

    public function other($class, $method = null, ...$args)
    {

        if(is_array($class))
        {
            if(count(func_get_args()) > 1)
            {
                ATool::insert($args, 0, $method);
            }
        }
        else
        {
            $class = [ $class, $method ];
        }
        
        $this->handler->other_method = $class;
        $this->handler->other_args = $args;

    }

}
