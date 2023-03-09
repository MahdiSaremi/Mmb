<?php
#auto-name
namespace Mmb\Listeners;

trait HasCustomMethod
{

    protected static $_custom_methods = [];

    /**
     * افزودن متد دلخواه استاتیک
     *
     * @param string $name
     * @param \Closure|string|array $callable
     * @return void
     */
    public static function addCustomMethod($name, $callable)
    {
        static::$_custom_methods[$name] = $callable;
    }

    protected function invokeCustomMethod($name, array $args, &$value = null)
    {
        if($callable = static::$_custom_methods[$name] ?? false)
        {
            $value = $callable($this, ...$args);
            return true;
        }
        return false;
    }

    public function __call($method, $args)
    {
        if($this->invokeCustomMethod($method, $args, $value))
            return $value;

        throw new \BadMethodCallException("Call to undefined method '$method' on '".static::class."'");
    }
    
}
