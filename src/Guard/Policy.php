<?php

namespace Mmb\Guard; #auto

class Policy
{

    public function boot()
    { }

    /**
     * افزودن دسترسی مورد نیاز برای کلاس مورد نظر
     * 
     * توحه کنید این ویژگی تنها برای کنترلرها و فرم ها می باشد
     * 
     * `$this->classNeedTo(App\User\Manage::class, 'access_users');`
     *
     * @param string $class
     * @param string $name
     * @return void
     */
    public function classNeedTo($class, $name)
    {
        app(Guard::class)->addClassNeedTo($class, $name);
    }

    /**
     * افزودن دسترسی مورد نیاز برای کلاس هایی با این پیشوند
     * 
     * توحه کنید این ویژگی تنها برای کنترلرها و فرم ها می باشد
     * 
     * `$this->classPrefixNeedTo('App\Panel\\', 'access_panel');`
     *
     * @param string $prefix
     * @param string $name
     * @return void
     */
    public function classPrefixNeedTo($prefix, $name)
    {
        app(Guard::class)->addClassPrefixNeedTo($prefix, $name);
    }

}
