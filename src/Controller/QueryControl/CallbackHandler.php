<?php

namespace Mmb\Controller\QueryControl; #auto

use Mmb\Controller\Handler\Handler;
use Mmb\Listeners\Listeners;
use Mmb\Update\Callback\Callback;

class CallbackHandler extends Handler
{

    /**
     * @var string
     */
    public $controller;
    
    public function __construct($controller)
    {
        $this->controller = $controller;
        $this->break();
    }

    private $call;
    
    public function check()
    {
        if (!parent::check())
            return false;
        
        if(Callback::$this)
        {

            $query = Callback::$this->data;

            $con = app($this->controller);
            $booter = $con->getCallbackBooter();

            if ($this->call = $booter->matchQuery($query))
                return true;

        }
    }

    public function handle()
    {

        return Listeners::invokeMethod($this->controller, $this->call[0], $this->call[1] ?: []);

    }

}
