<?php

namespace Mmb\Handlers; #auto

use Mmb\Debug\Debug;
use Mmb\Update\Chat\Chat;

class ErrorHandler {

    use Defaultable;

    /**
     * زمانی که اروری مدیریت نشده رخ می دهد اجرا می شود
     *
     * @param \Exception $exception
     * @return void
     */
    public function error($exception) {

        $trace = $exception->getTrace();
        $trace2 = explode("\n", $exception->getTraceAsString());
        $trace_str = "";
        foreach($trace as $i => $t){
            #if(substr($t['file'], -7) == 'Mmb.php')
            #    continue;
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
