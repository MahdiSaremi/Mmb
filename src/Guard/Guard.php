<?php

namespace Mmb\Guard; #auto

use Mmb\Listeners\Listeners;

class Guard
{

    private $_defines = [];
    private $_policies = [];

    /**
     * تعریف سطح دسترسی جدید
     * 
     * @param string $name
     * @param \Closure $callback
     * @return void
     */
    public function define($name, \Closure $callback)
    {
        $this->_defines[$name] = $callback;
    }

    /**
     * تعریف کلاس پلیسی جدید
     * 
     * @param string $class
     * @return void
     */
    public function definePolicy($class)
    {
        $this->_policies[] = $class;
    }

    private $cache = [];

    /**
     * بررسی می کند این سطح دسترسی وجود دارد
     * 
     * @param string $name
     * @param array $args
     * @return bool
     */
    public function allow($name, ...$args)
    {

        // Cache
        if(!$args && isset($this->cache[$name]))
        {
            return $this->cache[$name];
        }

        // Defined callback
        if(isset($this->_defines[$name]))
        {
            $clbk = $this->_defines[$name];
            $result = Listeners::callMethod($clbk, $args) ? true : false;

            if(!$args)
            {
                $this->cache[$name] = $result;
            }

            return $result;
        }

        // Policies
        foreach($this->_policies as $policy)
        {
            if(class_exists($policy) && $policy = app($policy))
            {
                if(method_exists($policy, $name))
                {
                    $result = Listeners::callMethod([$policy, $name], $args) ? true : false;

                    if(!$args)
                    {
                        $this->cache[$name] = $result;
                    }

                    return $result;
                }
            }
        }

        // Error
        throw new PolicyNotFoundException("Policy '$name' not defined");

    }

    private $notAllowed;

    /**
     * تنظیم می کند زمانی که دسترسی غیر مجاز است (در شرایط خاص) چه عملی انجام شود
     * 
     * @param \Closure $callback
     * @return void
     */
    public function notAllowed(\Closure $callback)
    {
        $this->notAllowed = $callback;
    }

    public function invokeNotAllowed()
    {
        if ($this->notAllowed)
            Listeners::callMethod($this->notAllowed, []);
    }


    /**
     * بررسی دسترسی
     *
     * @param string $name
     * @param mixed ...$args
     * @return boolean
     */
    public static function allowTo($name, ...$args)
    {
        return app(Guard::class)->allow($name, ...$args);
    }

}
