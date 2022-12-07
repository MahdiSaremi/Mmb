<?php

namespace Mmb\Controller; #auto

use Mmb\Controller\Controller;
use Mmb\Controller\StepHandler\Handlable;
use Mmb\Handlers\StartHandler;

abstract class StartController extends Controller
{

    /**
     * مدیریت کامند
     * 
     * @return Handlable|null
     */
    public function handle()
    {
        $start = StartHandler::defaultStatic()->fromCode(msg()->startCode);
        if($start && method_exists($this, $start[0]))
        {
            return $this->invoke(...$start);
        }

        return $this->invoke('start');
    }

    /**
     * کامند استارت
     * 
     * @return \Mmb\Controller\Handler\Command
     */
    public static function startCommand()
    {
        return static::command('/start', 'handle');
    }

    /**
     * ایجاد لینک
     * 
     * @param string $name
     * @param string $data
     * @return string
     */
    public static function createLink($name, $data)
    {
        return StartHandler::defaultStatic()->createLink($name, $data);
    }
    
    /**
     * مدیریت دستور شروع
     * 
     * @return Handlable|null
     */
    public abstract function start();

}