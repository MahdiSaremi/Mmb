<?php

namespace Mmb\Listeners; #auto

use Closure;
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

    public static function invokeMethod($class, $method, array $args = [])
    {

        if (is_string($class))
            $class = app($class);

        return self::callMethod([$class, $method], $args);

    }

    public static function invokeMethod2($method, array $args = [])
    {

        if (!is_array($method))
            return self::callMethod($method, $args);

        if (count($method) == 1)
            return self::callMethod($method, $args);
        
        return self::invokeMethod($method[0], $method[1], $args);

    }

    public static function callMethod($method, array $args = [])
    {
        $mustInstance = false;
        if(is_array($method))
        {
            if(count($method) > 1)
            {
                $pars = (new \ReflectionMethod(@$method[0], @$method[1]))->getParameters();
                
                // Event
                if(@$method[0] instanceof InvokeEvent)
                {
                    $result = null;
                    if($method[0]->eventInvoke($method[1], $args, $result))
                    {
                        return $result;
                    }
                }

            }
            else
            {
                $mustInstance = true;
                $cons = (new \ReflectionClass(@$method[0]))->getConstructor();
                $pars = $cons ? $cons->getParameters() : [];
            }
        }
        else
        {
            $pars = (new \ReflectionFunction($method))->getParameters();
        }

        $finalArgs = [];
        $argsCount = count($args);
        $i = 0;
        foreach($pars as $par)
        {
            $type = $par->getType();
            $type = "$type";

            // if($type && $i < $argsCount && eqi($type, typeOf(@$args[$i])))
            if($i < $argsCount)
            {
                $arg = @$args[$i++];
            }
            elseif($type && class_exists($type))
            {
                $arg = Instance::get($type);
            }
            elseif($type && interface_exists($type))
            {
                $arg = Instance::get($type);
                if (!$arg)
                    throw new TypeException("Interface '$type' has not instance value");
            }
            else
            {
                // if ($i >= $argsCount)
                    continue;
                // $arg = @$args[$i++];
            }

            $finalArgs[] = $arg;
        }
        while ($i < $argsCount)
            $finalArgs[] = $args[$i++];

        // Result

        if($mustInstance)
        {
            $type = @$method[0];
            return new $type(...$finalArgs);
        }

        return $method(...$finalArgs);
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
