<?php

namespace Mmb\Kernel; #auto

use Mmb\Controller\Handler\Handler;
use Mmb\Controller\StepHandler\Handlable;
use Mmb\Controller\StepHandler\StepHandler;
use Mmb\Exceptions\TypeException;
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
            $provider->invokeListen('booted');
        }
    }

    /**
     * مدیریت آپدیت
     *
     * @param UpdProvider $provider
     * @return void
     */
    public static function handleUpdate(UpdProvider $provider)
    {

        // Get update
        $upd = $provider->getUpdate();
        if(!$upd)
            return;

        // Events
        Provider::invokeAllListens('update', [ $upd ]);
        if (Provider::$updateCanceled)
            return;

        // Handle
        $handlers = $provider->getHandlers();
        $handlable = self::runHandlers($handlers);

        // Save step handler
        if($handlable)
        {
            StepHandler::set($handlable->getHandler());
        }

        // Events
        Provider::invokeAllListens('updateHandled', [ ]);
        
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

            $res = $handler->runHandle();

            if($res != null)
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
    
}
