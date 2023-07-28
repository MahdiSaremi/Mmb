<?php
#auto-name
namespace Mmb\Core;

use Closure;
use Mmb\Debug\Debug;
use Mmb\ExtraThrow\ExtraException;
use Mmb\Listeners\HasNormalStaticListeners;
use Mmb\Update\Chat\Chat;
use Throwable;

class ErrorHandler
{

    use Defaultable;

    protected $catches = [];

    /**
     * افزودن کالبک برای ارور کلاس مشخص
     * 
     * `ErrorHandler::defaultStatic()->catchOf(MyException::class, function(MyException $exception) { replyText("خطای 'دلخواه' رخ داد"); });`
     * 
     * @param string $class
     * @param Closure $callback
     * @return void
     */
    public function catchOf(string $class, Closure $callback)
    {
        $this->catches[$class] = $callback;
    }

    /**
     * زمانی که اروری مدیریت نشده رخ می دهد اجرا می شود
     *
     * @param Throwable $exception
     * @return void
     */
    public function error($exception)
    {
        // Extra exception
        if($exception instanceof ExtraException)
        {
            $exception->invoke();
            return;
        }

        // Event
        $event = false;
        foreach($this->catches as $class => $callback)
        {
            if($exception instanceof $class)
            {
                $event = $callback;
                break;
            }
        }

        if($event)
        {
            $event($exception);
            return;
        }
        
        // Listener handling
        if(static::invokeListeners('errorHandling', [ $exception ], 'first-true'))
        {
            // None
        }
        // Default handling
        else
        {
            $trace = $exception->getTrace();
            $trace2 = explode("\n", $exception->getTraceAsString());
            $trace_str = "";
            foreach($trace as $i => $t) {
                $file = $t['file'];
                $line = $t['line'];
                $text = str_replace(["#$i ", "$file($line): ", "$file"], '', $trace2[$i]);
                $trace_str .= "\n    On $text\n        File: $file\tLine: $line";
            }
            $error = $exception->getMessage();
            mmb_log("You have an unhandled exception: $error$trace_str");
        }
        
        // Debug mode
        if(Debug::isOn())
        {
            // Listener debugging
            if(static::invokeListeners('errorDebugging', [ $exception ], 'first-true'))
            {
                // None
            }
            // Default debugging
            else
            {
                if(Chat::$this)
                {
                    Chat::$this->sendMsg([
                        'text' => "<b>You have an unhandled exception:</b> " . htmlEncode($error),
                        'mode' => "HTML",
                        'ignore' => true,
                    ]);
                }
            }
        }

        static::invokeListeners('errorHandled', [ $exception ]);
    }

    /**
     * افزودن شنونده ای که زمانی که این نوع خطا ترو شد و هندل نشد صدا زده شود
     *
     * @param string $class
     * @param Closure $callback `function($exception)`
     * @return void
     */
    public static function catching(string $class, Closure $callback)
    {
        static::defaultStatic()->catchOf($class, $callback);
    }

    use HasNormalStaticListeners;

    /**
     * افزودن شنونده ای که زمان مدیریت خطا صدا زده می شود
     * 
     * زمانی که یک شنونده مقدار ترویی را برگرداند، دیگر متد ها و عملیات های دیگر اجرا نمی شود
     *
     * @param Closure $callback `function(Throwable $exception)`
     * @return void
     */
    public static function errorHandling(Closure $callback)
    {
        static::listen(__FUNCTION__, $callback);
    }
    
    /**
     * افزودن شنونده ای که در انتهاب مدیریت شدن خطا صدا زده می شود
     *
     * @param Closure $callback `function(Throwable $exception)`
     * @return void
     */
    public static function errorHandled(Closure $callback)
    {
        static::listen(__FUNCTION__, $callback);
    }

    /**
     * افزودن شنونده ای که در زمان مدیریت خطا در حالت دیباگ مد صدا زده می شود
     * 
     * این تابع فارغ از متد مدیریت خطاها اجرا می شود
     * 
     * زمانی که یک شنونده مقدار ترویی را برگرداند، دیگر متد ها و عملیات های دیگر اجرا نمی شود
     *
     * @param Closure $callback
     * @return void
     */
    public static function errorDebugging(Closure $callback)
    {
        static::listen(__FUNCTION__, $callback);
    }

}
