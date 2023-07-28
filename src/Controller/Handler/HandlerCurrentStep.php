<?php

namespace Mmb\Controller\Handler; #auto

use Mmb\Controller\StepHandler\Handlable;
use Mmb\Controller\StepHandler\StepHandler;

class HandlerCurrentStep extends Handler
{
    
    // public function __construct()
    // {
    //     $this->break();
    // }

    protected static $ignoreStepBreak = false;
    public static function ignoreStepBreak()
    {
        static::$ignoreStepBreak = true;
    }

    public static function cancelIgnoreStepBreak()
    {
        static::$ignoreStepBreak = false;
    }

    public static function getIgnoredStepBreak()
    {
        return static::$ignoreStepBreak;
    }

    public function check()
    {
        return parent::check() && StepHandler::get();
    }
    
	/**
	 * مدیریت آپدیت
	 * @return Handlable|null
	 */
	public function handle()
    {
        static::$ignoreStepBreak = false;
        $step = StepHandler::get();
        $result = $step->handle();

        if(!static::$ignoreStepBreak)
        {
            $this->stop();
            return $result;
        }
	}

}
