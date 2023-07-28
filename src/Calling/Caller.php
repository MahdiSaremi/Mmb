<?php
#auto-name
namespace Mmb\Calling;

use Closure;
use Iterator;
use Mmb\Exceptions\TypeException;
use Mmb\Kernel\Instance;
use Mmb\Listeners\InvokeEvent;
use Mmb\Mapping\Arr;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;

class Caller
{
    
    /**
     * صدا زدن متدی از کلاس مورد نظر
     * 
     * اگر اسم کلاس وارد شود، آبجکت عمومی آن را میسازد و از طریق آن صدا می زند
     *
     * @param string|object $class
     * @param string $method
     * @param array|Arr $args
     * @param boolean $silentMode
     * @return mixed
     */
    public static function invoke(string|object $class, string $method, array|Arr $args = [], bool $silentMode = false)
    {

        if (is_string($class))
            $class = app($class);

        return self::call([$class, $method], $args, $silentMode);

    }

    /**
     * صدا زدن تابع
     * 
     * اگر اسم کلاسی را وارد کرده باشید، آبجکت عمومی آن را میسازد و از طریق آن صدا می
     *
     * @param array|string|Closure $method
     * @param array|Arr $args
     * @param boolean $silentMode
     * @return mixed
     */
    public static function invoke2(array|string|Closure $method, array|Arr $args = [], bool $silentMode = false)
    {

        if (!is_array($method))
            return self::call($method, $args, $silentMode);

        if (count($method) == 1)
            return self::call($method, $args, $silentMode);
        
        return self::invoke($method[0], $method[1], $args, $silentMode);

    }

    /**
     * صدا زدن تابع مورد نظر
     *
     * @param string|array|Closure $method
     * @param array|Arr $args
     * @param boolean $silentMode
     * @return mixed
     */
    public static function call(string|array|Closure $method, array|Arr $args = [], bool $silentMode = false)
    {
        $mustInstance = false;
        $callEvents = [];
        if($args instanceof Arr)
        {
            $args = $args->toArray();
        }
        if(is_array($method))
        {
            $reflectionClass = new ReflectionClass(@$method[0]);
            if(count($method) > 1)
            {
                $reflection = $reflectionClass->getMethod($method[1]);
                $pars = $reflection->getParameters();
                
                // Event
                if(!$silentMode && @$method[0] instanceof InvokeEvent)
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
                $pars = $reflectionClass->getConstructor()?->getParameters() ?? [];
            }
        }
        else
        {
            $reflection = new ReflectionFunction($method);
            $pars = $reflection->getParameters();
        }



        $finalArgs = [];
        $stopCounter = false;
        foreach($pars as $par)
        {
            $type = $par->getType();
            if($type instanceof ReflectionNamedType)
            {
                $type = $type->getName();
            }
            else
            {
                $type = null;
            }
            $name = $par->getName();

            // fn(...$args)
            if($par->isVariadic())
            {
                break;
            }
            // [ 'Arg' ]
            elseif(array_key_exists($position = $par->getPosition(), $args))
            {
                if($stopCounter)
                {
                    $finalArgs[$name] = $args[$position];
                }
                else
                {
                    $finalArgs[] = $args[$position];
                }
                unset($args[$position]);
            }
            // [ 'name' => 'Arg' ]
            elseif(array_key_exists($name, $args))
            {
                $finalArgs[$name] = $args[$name];
                unset($args[$name]);
            }
            // fn($demo = default)
            elseif($par->isOptional())
            {
                $stopCounter = true;
            }
            // fn(Class $class)
            elseif($type && class_exists($type))
            {
                $finalArgs[$name] = Instance::get($type);
                unset($args[$name]);
                
            }
            // fn(Interface $object)
            elseif($type && interface_exists($type))
            {
                $finalArgs[$name] = $value = Instance::get($type);
                if (!$value)
                    throw new TypeException("Interface '$type' has not instance value");
                unset($args[$name]);
            }
        }
        $finalArgs = [ ...$finalArgs, ...$args ];

        // Result

        if($mustInstance)
        {
            $type = @$method[0];
            return new $type(...$finalArgs);
        }

        return $method(...$finalArgs);
    }

}
