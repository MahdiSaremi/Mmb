<?php

namespace Mmb\Controller; #auto

use Mmb\Controller\StepHandler\Handlable;
use Mmb\Controller\StepHandler\MenuHandler;
use Mmb\Tools\ATool;

class Menu implements Handlable
{

    /** @var MenuHandler */
    private $handler;
    private $key;

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
        $this->handler->setKeys($keys);
        $this->key = $keys;
    }

    /**
     * تابعی که زمانی که هیچکدام از گزینه ها انتخاب نشد اجرا می شود
     * 
     * @param string|array $class
     * @param string $method
     * @param mixed ...$args
     * @return $this
     */
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

        return $this;

    }

    public function getKey()
    {

        $res = [];
        foreach($this->key as $row)
        {
            $keyr = [];

            if($row)
            foreach($row as $btn)
            {
                if($btn)
                    $keyr[] = $this->getSingleKey($btn);
            }

            if($keyr)
                $res[] = $keyr;
        }

        return $res;
    }

    public function getSingleKey($key)
    {

        unset($key['method']);
        unset($key['args']);

        return $key;

    }

}
