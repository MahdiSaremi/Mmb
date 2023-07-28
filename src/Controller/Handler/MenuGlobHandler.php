<?php
#auto-name
namespace Mmb\Controller\Handler;

use Mmb\Controller\Menu;
use Mmb\Exceptions\MmbException;
use Mmb\Listeners\Listeners;

class MenuGlobHandler extends Handler
{

    public $method;

    public function __construct($method)
    {
        $this->method = $method;
    }

    public function handle()
    {
        HandlerCurrentStep::cancelIgnoreStepBreak();
        
        $menu = Listeners::invokeMethod2($this->method);
        if(!($menu instanceof Menu))
        {
            throw new MmbException("Handler global menu not found");
        }

        $menu->other(null);
        $result = $menu->getHandler()->handle();

        if(!HandlerCurrentStep::getIgnoredStepBreak())
            $this->stop();

        return $result;
    }

}
