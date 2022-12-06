<?php

namespace Mmb\Controller\Handler; #auto

use Mmb\Controller\StepHandler\Handlable;
use Mmb\Listeners\Listeners;

class Command extends Handler
{

    /**
     * ساخت مدیر کامند جدید
     * 
     * @param string|array $command کامند(ها) که باید با اسلش شروع شوند
     * @param mixed $controller
     * @param mixed $method
     * @return static
     */
    public static function command($command, $controller, $method = null)
    {
        return new static ($command, $controller, $method);
    }

    private $commands;
    private $controller;

    /**
     * @param string|array $command
     * @param mixed $controller
     * @param mixed $method
     * @return static
     */
    public function __construct($command, $controller, $method = null)
    {

        if (!is_array($command))
            $command = [$command];

        if (!is_array($controller))
            $controller = [$controller, $method];

        $this->commands = $command;
        $this->controller = $controller;

        $this->break();

    }

    /**
     * @var bool
     */
    public $ignoreCase = true;
    
    /**
     * حساسیت به بزرگی و کوچکی حروف
     * 
     * @return $this
     */
    public function dontIgnoreCase()
    {
        $this->ignoreCase = false;
        return $this;
    }
    
	public function check()
    {
        
        if (!parent::check())
            return false;
        
        $msg = msg();
        if (!$msg || $msg->type != 'text')
            return false;
        
        foreach($this->commands as $command)
        {
         
            if(@$command[0] == '/')
            {
                if($msg->isCommand(substr($command, 1), $this->ignoreCase))
                {
                    return true;
                }
            }
            elseif($this->ignoreCase)
            {
                if(eqi($msg->text, $command))
                {
                    return true;
                }
            }
            else
            {
                if($msg->text == $command)
                {
                    return true;
                }
            }
            
        }

        return false;

	}

    public function handle()
    {

        return Listeners::invokeMethod2($this->controller);

    }

}
