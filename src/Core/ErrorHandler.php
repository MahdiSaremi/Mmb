<?php
#auto-name
namespace Mmb\Core;

use Mmb\Debug\Debug;
use Mmb\Update\Chat\Chat;

class ErrorHandler {

    use Defaultable;

    protected $catches = [];

    /**
     * افزودن کالبک برای ارور کلاس مشخص
     * 
     * `ErrorHandler::defaultStatic()->catchOf(MyException::class, function(MyException $exception) { replyText("خطای 'دلخواه' رخ داد"); });`
     * 
     * @param string $class
     * @param \Closure $callback
     * @return void
     */
    public function catchOf($class, $callback)
    {
        $this->catches[$class] = $callback;
    }

    /**
     * زمانی که اروری مدیریت نشده رخ می دهد اجرا می شود
     *
     * @param \Exception $exception
     * @return void
     */
    public function error($exception) {

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

        }

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

            if(Debug::isOn() && Chat::$this)
                Chat::$this->sendMsg([
                    'text' => "You have an unhandled exception: $error",
                    'ignore' => true,
                ]);

        }

    }

}
