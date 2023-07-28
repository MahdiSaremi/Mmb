<?php

namespace Mmb\Kernel; #auto

use Mmb\Listeners\Listeners;

class Instance
{

    private static $instances = [];

    private static $creators = [];

    public static function reset()
    {
        static::$instances = [];
        static::$creators = [];
    }

    /**
     * گرفتن مقدار عمومی
     *
     * @template T
     * @param string|class-string<T> $class
     * @return mixed|T
     */
    public static function get($class, $createOnFail = true)
    {
        if(isset(self::$instances[$class]))
        {
            return self::$instances[$class];
        }

        if(property_exists($class, 'this'))
        {
            return $class::$this;
        }

        if(!$createOnFail)
        {
            return null;
        }

        if(isset(self::$creators[$class]))
        {
            self::$instances[$class] = false;
            return self::$instances[$class] = Listeners::callMethod(self::$creators[$class], []);
        }

        if(method_exists($class, 'instance'))
        {
            return $class::instance();
        }

        if(property_exists($class, 'this'))
        {
            return $class::$this;
        }

        return self::$instances[$class] = Listeners::callMethod([$class], []);
    }

    public static function set($class, $object)
    {
        self::$instances[$class] = $object;
    }

    public static function setOn($class, $callback)
    {
        self::$creators[$class] = $callback;
    }

    public static function changeCacheInstance($class, $object)
    {
        $before = self::$instances[$class] ?? null;
        if($object === null)
        {
            unset(self::$instances[$class]);
        }
        else
        {
            self::$instances[$class] = $object;
        }
        return $before;
    }
    
}
