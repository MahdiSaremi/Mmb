<?php
#auto-name
namespace Mmb\Listeners;

trait HasCustomStaticMethod
{

    protected static $_custom_static_methods = [];

    /**
     * افزودن متد دلخواه استاتیک
     *
     * @param string $name
     * @param \Closure|string|array $callable
     * @return void
     */
    public static function addCustomStaticMethod($name, $callable)
    {
        static::$_custom_static_methods[$name] = $callable;
    }

    protected static function invokeCustomStaticMethod($name, array $args, &$value = null)
    {
        if($callable = static::$_custom_static_methods[$name] ?? false)
        {
            $value = $callable(...$args);
            return true;
        }
        return false;
    }

    public static function __callStatic($method, $args)
    {
        if(static::invokeCustomStaticMethod($method, $args, $value))
            return $value;

        throw new \BadMethodCallException("Call to undefined static method '$method' on '".static::class."'");
    }
    
}
