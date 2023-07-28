<?php
#auto-name
namespace Mmb\FakeFace;

use Closure;
use Mmb\Db\Table\Table;
use Mmb\Kernel\Instance;
use Mmb\Update\Callback\Callback;
use Mmb\Update\Chat\Chat;
use Mmb\Update\Inline\ChosenInline;
use Mmb\Update\Inline\Inline;
use Mmb\Update\Message\Msg;
use Mmb\Update\Upd;
use Mmb\Update\User\UserInfo;

/**
 * این کلاس برای زمان موقت دیدگاه کل سورس را تغییر می دهد
 */
class FakeFace
{

    /**
     * تغییر دیدگاه - تغییر مقدار های instance
     *
     * @param array $class_instance
     * @param Closure|array|string $callback
     * @return mixed
     */
    public static function face(array $class_instance, Closure|array|string $callback)
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
     * @param Closure|array|string $callback
     * @return void
     */
    public static function user(Table $user, Closure|array|string $callback, $userid = null)
    {
        $step = static::face([
            get_class($user) => $user,
            Upd::class => null,
            Msg::class => Msg::of($userid ?? $user->id, 0),
            Chat::class => Chat::of($userid ?? $user->id),
            UserInfo::class => UserInfo::of($userid ?? $user->id),
            Callback::class => null,
        ], $callback);

        if($step !== null)
            $user->setStep($step);
    }
    
}
