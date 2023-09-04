<?php

namespace Mmb\Listeners; #auto

use Closure;
use InvalidArgumentException;

trait HasStaticListeners
{

    private static $listeners_static = [];

    /**
     * شونده ای برای این ایونت تعریف می کند
     *
     * @param string $name
     * @param Closure|array|string $callback
     * @return void
     */
    public static function listenStatic(string $name, Closure|array|string $callback)
    {
        @self::$listeners_static[self::class][$name][] = $callback;
    }

    /**
     * شنونده های این ایونت را صدا می زند
     * 
     * نوع های پشتیبانی شده:
     * 
     * `null` : هیچ مقداری را بر نمی گرداند
     * 
     * `last` : آخرین مقدار را بر می گرداند
     * 
     * `first-true` : اولین مقداری که ترو (یا مشابه) باشد را بر می گرداند
     * 
     * `first-is-true` : اولین مقداری که دقیقا ترو باشد را بر می گرداند
     *
     * `first-false` : اولین مقداری که فالس (یا مشابه) باشد را بر می گرداند
     * 
     * `first-is-false` : اولین مقداری که دقیقا فالس باشد را بر می گرداند
     *
     * `first-not-null` : اولین مقداری که نال نباشد را بر می گرداند
     * 
     * `last-not-null` : آخرین مقداری که نال نباشد را بر می گرداند
     * 
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public static function invokeStaticListeners(string $name, array $args = [], string $returnType = 'null')
    {
        $listeners = self::$listeners_static[self::class][$name] ?? [];
        return Listeners::invokeCustomListener($listeners, $args, $returnType);
    }
    
}
