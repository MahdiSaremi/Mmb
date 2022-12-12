<?php

namespace Mmb\Listeners; #auto

trait HasStaticListeners
{

    private static $listeners_static = [];

    public static function listenStatic($name, $callback)
    {
        @static::$listeners_static[static::class][$name][] = $callback;
    }

    public static function invokeListenStatic($name, array $args = [])
    {
        $listeners = static::$listeners_static[static::class][$name] ?? [];

        foreach($listeners as $listener)
        {
            Listeners::callMethod($listener, $args);
        }
    }
    
}
