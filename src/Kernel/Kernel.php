<?php

namespace Mmb\Kernel; #auto

use Mmb\Controller\Controller;
use Mmb\Controller\Handler\Handler;
use Mmb\Controller\Handler\HandlerGroup;
use Mmb\Controller\StepHandler\Handlable;
use Mmb\Controller\StepHandler\StepHandler;
use Mmb\Db\Table\Table;
use Mmb\Exceptions\TypeException;
use Mmb\Lang\Lang;
use Mmb\Provider\Provider;
use Mmb\Update\Upd;
use Providers\UpdProvider;

class Kernel 
{

    public static function bootstrap()
    {
        self::register();
        self::boot();
    }

    private static function register()
    {
        // Providers
        Provider::registerAll();
    }

    private static function boot()
    {
        // Providers
        Provider::bootAll();

        // Booted
        foreach(Provider::getAllObjects() as $provider)
        {
            $provider->invokeListeners('booted');
        }
    }

    /**
     * مدیریت آپدیت
     *
     * @param UpdProvider $provider
     * @param Upd|null $upd
     * @return void
     */
    public static function handleUpdate(UpdProvider $provider, Upd $upd = null)
    {

        // Get update
        if(is_null($upd))
        {
            mmb()->loading_update = true;
            $upd = $provider->getUpdate();
            mmb()->loading_update = false;
        }
        if(!$upd)
            return;

        // Events
        Provider::$updateCanceled = false;
        Provider::invokeAllListeners('update', [ $upd ]);
        if (Provider::$updateCanceled)
            return;

        // Handle
        $handlers = $provider->getHandlers();
        $handlable = self::runHandlers($handlers);

        // Filter
        if($handlable)
        {
            $handlable = static::filterAutoHandle($handlable);
        }

        // Save step handler
        if($handlable)
        {
            StepHandler::set($handlable->getHandler());
        }

        // Events
        Provider::invokeAllListeners('updateHandled', [ ]);
        
    }

    /**
     * ریست کردن مقادیر اصلی
     *
     * @deprecated 0
     * @return void
     */
    public static function reset()
    {
        // Controller::resetCache();
        Table::resetCache();
        Lang::resetCache();
        Instance::reset();
        Provider::resetInstances();
    }

    /**
     * 
     * @param array<Handler|null> $handlers
     * @return Handlable|null
     */
    private static function runHandlers($handlers)
    {
        $last = null;
        foreach($handlers as $handler)
        {
            
            if ($handler == null)
                continue;

            if (is_string($handler))
                $handler = new $handler;

            if (!($handler instanceof Handler))
                throw new TypeException("Handler must be Handler object, given " . typeOf($handler));

            if($handler instanceof HandlerGroup)
            {
                $res = static::runHandlers($handler->getHandlers());
            }
            else
            {
                $res = $handler->runHandle();
            }

            if($res != null && $res instanceof Handlable)
            {
                $last = $res;
            }

        }
        return $last;
    }
    

    /**
     * زمان شروع فعالیت
     *
     * @var float
     */
    public static $runTime;

    private static $_run_long = false;

    /**
     * بررسی طولانی بودن پروسه
     *
     * @return bool
     */
    public static function runIsLong()
    {
        if(self::$_run_long)
            return true;
        if(microtime(true) - self::$runTime >= 2)
        {
            self::$_run_long = true;
            return true;
        }
        return false;
    }

    /**
     * فیلتر کردن هندلر
     * 
     * در صورتی که متد اتوهندل وجود داشته باشد، آن را صدا می زند
     *
     * @param Handlable|mixed $handlable
     * @return Handlable|mixed
     */
    private static function filterAutoHandle($handlable)
    {
        if(method_exists($handlable, '__autoHandle'))
        {
            return $handlable->__autoHandle();
        }

        return $handlable;
    }
    
}
