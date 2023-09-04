<?php
#auto-name
namespace Mmb\Listeners;

use Closure;
use InvalidArgumentException;

trait HasStaticClassNormalListeners
{

    private static $listeners_static_class = [];

    /**
     * شونده ای برای این ایونت تعریف می کند
     *
     * @param string $name
     * @param Closure|array|string $callback
     * @return void
     */
    public static function listen(string $name, Closure|array|string $callback)
    {
        @static::$listeners_static_class[static::class][$name][] = $callback;
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
    public static function invokeListeners(string $name, array $args = [], string $returnType = 'null')
    {
        $listeners = static::$listeners_static_class[static::class][$name] ?? [];
        return Listeners::invokeCustomListener($listeners, $args, $returnType);
    }
    
}
