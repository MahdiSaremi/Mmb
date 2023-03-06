<?php
#auto-name
namespace Mmb\FakeFace;

use Mmb\Db\Table\Table;
use Mmb\Kernel\Instance;
use Mmb\Update\Chat\Chat;
use Mmb\Update\Message\Msg;
use Mmb\Update\User\User;

/**
 * این کلاس برای زمان موقت دیدگاه کل سورس را تغییر می دهد
 */
class FakeFace
{

    /**
     * تغییر دیدگاه - تغییر مقدار های instance
     *
     * @param array $class_instance
     * @param callable|\Closure $callback
     * @return mixed
     */
    public static function face(array $class_instance, $callback)
    {
        foreach($class_instance as $class => $instance)
            $class_instance[$class] = Instance::changeCacheInstance($class, $instance);

        $result = $callback();

        foreach($class_instance as $class => $instance)
            $class_instance[$class] = Instance::changeCacheInstance($class, $instance);

        return $result;
    }

    /**
     * تغییر دیدگاه به کاربر
     * 
     * مقداری که ریترن می شود را به عنوان استپ کاربر تنظیم می کند
     *
     * @param Table $user
     * @param callable|\Closure $callback
     * @return void
     */
    public static function user(Table $user, $callback)
    {
        $step = static::face([
            get_class($user) => $user,
        ], $callback);

        if($step !== null)
            $user->setStep($step);
    }
    
}
