<?php
#auto-name
namespace Mmb\Controller\Handler;

use Mmb\ExtraThrow\ExtraException;

class GoToNextHandlerException extends ExtraException
{

    public function __construct()
    {
        Handler::$requireStop = false;
    }

    public function invoke()
    {
    }
    
}
