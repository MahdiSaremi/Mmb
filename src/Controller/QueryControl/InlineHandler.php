<?php

namespace Mmb\Controller\QueryControl; #auto

use Mmb\Controller\Handler\Handler;
use Mmb\Listeners\Listeners;
use Mmb\Update\Inline\Inline;

class InlineHandler extends Handler
{
    
    /**
     * @var string
     */
    public $controller;
    
    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    private $call;
    
    public function check()
    {
        if (!parent::check())
            return false;
        
        if(Inline::$this)
        {

            $query = Inline::$this->query;

            $con = app($this->controller);
            $booter = $con->getInlineBooter();

            if ($this->call = $booter->matchQuery($query))
                return true;

        }
    }

    public function handle()
    {

        return Listeners::invokeMethod($this->controller, $this->call[0], $this->call[1] ?: []);

    }

}
