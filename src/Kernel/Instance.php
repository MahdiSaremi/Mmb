<?php

namespace Mmb\Kernel; #auto

use Mmb\Listeners\Listeners;

class Instance
{

    private static $instances = [];

    private static $creators = [];

    /**
     * گرفتن مقدار عمومی
     *
     * @param string $class
     * @return mixed
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
    
}
