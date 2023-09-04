<?php

namespace Mmb\Listeners; #auto

use Closure;
use InvalidArgumentException;
use Mmb\Calling\Caller;
use Mmb\Exceptions\TypeException;
use Mmb\Kernel\Instance;

class Listeners
{
    
    private static $all = [];
    private static $all_queue = [];

    /**
     * افزودن شنونده دلخواه با نام
     *
     * @param string $name نام شنونده
     * @param Closure|string|array $callback `function(...)`
     * @param boolean $queue قرار گیری در صف
     * @return void
     */
    public static function listen($name, $callback, $queue = false)
    {
        if(!($callback instanceof Closure || is_callable($callback))){
            throw new TypeException("The callback type is invalid, Callable/Closure required");
        }
        if($queue)
            self::$all_queue[$name][] = $callback;
        else
            self::$all[$name][] = $callback;
    }

    /**
     * اجرای شنونده های دلخواه
     *
     * @param string $name نام شنونده
     * @param mixed ...$args
     * @return bool
     */
    public static function run($name, ...$args)
    {
        $continue = true;
        foreach(self::$all[$name] ?? [] as $callback)
        {
            if($callback(...$args) === false)
                $continue = false;
        }
        if($continue)
        foreach(self::$all_queue[$name]??[] as $callback)
        {
            if($callback(...$args) === false)
                return false;
        }
        return $continue;
    }

    /**
     * صدا زدن متدی از کلاس مورد نظر
     * 
     * اگر اسم کلاس وارد شود، آبجکت عمومی آن را میسازد و از طریق آن صدا می زند
     *
     * @param string|object $class
     * @param string $method
     * @param array $args
     * @param boolean $silentMode
     * @return mixed
     */
    public static function invokeMethod(string|object $class, string $method, array $args = [], bool $silentMode = false)
    {
        return Caller::invoke($class, $method, $args, $silentMode);
    }

    /**
     * صدا زدن تابع
     * 
     * اگر اسم کلاسی را وارد کرده باشید، آبجکت عمومی آن را میسازد و از طریق آن صدا می
     *
     * @param array|string|Closure $method
     * @param array $args
     * @param boolean $silentMode
     * @return void
     */
    public static function invokeMethod2(array|string|Closure $method, array $args = [], bool $silentMode = false)
    {
        return Caller::invoke2($method, $args, $silentMode);
    }

    /**
     * صدا زدن تابع مورد نظر
     *
     * @param string|array|Closure $method
     * @param array $args
     * @param boolean $silentMode
     * @return mixed
     */
    public static function callMethod(string|array|Closure $method, array $args = [], bool $silentMode = false)
    {
        return Caller::call($method, $args, $silentMode);
    }

    // /**
    //  * اجرای شنونده های دلخواه (2)
    //  *
    //  * @param string $name نام شنونده
    //  * @param mixed &...$args
    //  * @return bool
    //  */
    // public static function run2($name, &...$args)
    // {
    //     $continue = true;
    //     foreach(self::$all[$name]??[] as $callback){
    //         if($callback(...$args) === false)
    //             $continue = false;
    //     }
    //     if($continue)
    //     foreach(self::$all_queue[$name]??[] as $callback){
    //         if($callback(...$args) === false)
    //             return false;
    //     }
    //     return $continue;
    // }

    public const R_NULL = 'null';
    public const R_LAST = 'last';
    public const R_FIRST_TRUE = 'first-true';
    public const R_FIRST_IS_TRUE = 'first-is-true';
    public const R_FIRST_FALSE = 'first-false';
    public const R_FIRST_IS_FALSE = 'first-is-false';
    public const R_FIRST_NOT_NULL = 'first-not-null';
    public const R_LAST_NOT_NULL = 'last-not-null';

    /**
     * آرایه ای از شنونده ها را صدا می زند
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
     * @param array $listeners
     * @param array $args
     * @param string $returnType
     * @return mixed
     */
    public static function invokeCustomListener(array $listeners, array $args = [], string $returnType = 'null')
    {
        switch($returnType)
        {
            case 'null':
                foreach($listeners as $listener)
                {
                    Listeners::callMethod($listener, $args);
                }
            break;

            case 'last':
                $last = null;
                foreach($listeners as $listener)
                {
                    $last = Listeners::callMethod($listener, $args);
                }
                return $last;

            case 'last-not-null':
                $last = null;
                foreach($listeners as $listener)
                {
                    if(($value = Listeners::callMethod($listener, $args)) !== null)
                    {
                        $last = $value;
                    }
                }
                return $last;
    
            case 'first-true':
                foreach($listeners as $listener)
                {
                    if($value = Listeners::callMethod($listener, $args))
                    {
                        return $value;
                    }
                }
            break;

            case 'first-is-true':
                foreach($listeners as $listener)
                {
                    if(Listeners::callMethod($listener, $args) === true)
                    {
                        return true;
                    }
                }
            break;

            case 'first-false':
                foreach($listeners as $listener)
                {
                    if(!($value = Listeners::callMethod($listener, $args)))
                    {
                        return $value;
                    }
                }
            break;

            case 'first-is-false':
                foreach($listeners as $listener)
                {
                    if(Listeners::callMethod($listener, $args) === false)
                    {
                        return false;
                    }
                }
            break;

            case 'first-not-null':
                foreach($listeners as $listener)
                {
                    if(!is_null($value = Listeners::callMethod($listener, $args)))
                    {
                        return $value;
                    }
                }
            break;

            default:
                throw new InvalidArgumentException("Unknown \$returnType value, given '{$returnType}'");
        }
    }

}
