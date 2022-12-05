<?php

namespace Mmb\Tools; #auto

trait Staticable
{

    public static $_instances = [];

    /**
     * گرفتن آبجکت این کلاس
     *
     * @return static
     */
    public static function instance()
    {
        if(isset(static::$_instances[static::class])) {
            return static::$_instances[static::class];
        }
        else {
            return static::$_instances[static::class] = new static();
        }
    }

    public static function __callStatic($method, $args)
    {
        return static::instance()->$method(...$args);
    }

}
