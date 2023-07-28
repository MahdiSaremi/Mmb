<?php

namespace Mmb\Controller\StepHandler; #auto

use Mmb\Listeners\Listeners;

class NextRun extends StepHandler
{

    public $method;
    public $args;

    public function __sleep()
    {
        return $this->getSleepNotNull();
    }

    public function __construct($method, ...$args)
    {
        $this->method = $method;
        $this->args = $args;
    }

    public function handle()
    {
        return Listeners::invokeMethod2($this->method, $this->args);
    }

}
