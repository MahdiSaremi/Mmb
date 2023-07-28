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


    protected $_policy_booted = false;
    
    /**
     * بررسی می کند کلاس مورد نظر طبق پترن های تنظیم شده دسترسی دارد یا خیر
     *
     * @param string $class
     * @return boolean
     */
    public function allowClass($class)
    {
        // Boot
        if(!$this->_policy_booted)
        {
            $this->_policy_booted = true;
            foreach($this->_policies as $policy)
            {
                app($policy)->boot();
            }
        }

        // Class check
        if($needs = $this->_class_need_to[$class] ?? false)
            foreach($needs as $need)
                if(!$this->allow($need))
                    return false;
        
        // Class prefix check
        foreach($this->_class_prefix_need_to as $prefix => $needs)
        {
            if(startsWith($class, $prefix, true))
            {
                foreach($needs as $need)
                {
                    if(!$this->allow($need))
                        return false;
                }
            }
        }

        return true;
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

    protected $_class_need_to = [];
    /**
     * افزودن دسترسی مورد نیاز برای کلاس مورد نظر
     * 
     * توحه کنید این ویژگی تنها برای کنترلرها و فرم ها می باشد
     * 
     * `app(Guard::class)->addClassNeedTo(App\User\Manage::class, 'access_users');`
     *
     * @param string $class
     * @param string $name
     * @return void
     */
    public function addClassNeedTo($class, $name)
    {
        @$this->_class_need_to[$class][] = $name;
    }

    protected $_class_prefix_need_to = [];
    /**
     * افزودن دسترسی مورد نیاز برای کلاس هایی با این پیشوند
     * 
     * توحه کنید این ویژگی تنها برای کنترلرها و فرم ها می باشد
     * 
     * `app(Guard::class)->addClassPrefixNeedTo('App\Panel\\', 'access_panel');`
     *
     * @param string $prefix
     * @param string $name
     * @return void
     */
    public function addClassPrefixNeedTo($prefix, $name)
    {
        $prefix = ltrim($prefix, '\\');
        @$this->_class_prefix_need_to[$prefix][] = $name;
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

    /**
     * نیاز به دسترسی
     * 
     * در صورت عدم وجود دسترسی، ارور مربوطه را ارسال می کند
     *
     * @param string $name
     * @param mixed ...$args
     * @return void
     */
    public static function required($name, ...$args)
    {
        if(!app(Guard::class)->allow($name, ...$args))
        {
            throw new AccessDaniedException(null);
        }
    }

}
