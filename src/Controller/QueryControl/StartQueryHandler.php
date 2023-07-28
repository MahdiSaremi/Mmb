<?php
#auto-name
namespace Mmb\Controller\QueryControl;

use Mmb\Controller\Handler\Handler;
use Mmb\Listeners\Listeners;
use Mmb\Update\Message\Msg;

class StartQueryHandler extends Handler
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
        
        if(msg() && msg()->isCommand('start'))
        {
            $query = msg()->commandData;

            $con = app($this->controller);
            $booter = $con->getStartBooter();

            if ($this->call = $booter->matchQuery($query))
                return true;

        }
        
        return false;
    }

    public function handle()
    {

        return Listeners::invokeMethod($this->controller, $this->call[0], $this->call[1] ?: []);

    }

}
