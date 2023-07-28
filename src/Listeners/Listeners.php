<?php

namespace Mmb\Listeners; #auto

use Closure;
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

}
