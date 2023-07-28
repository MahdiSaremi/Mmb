<?php

namespace Mmb\Provider; #auto

use Mmb\Controller\Handler\HandlerStep;
use Mmb\Controller\StepHandler\StepHandler;

class UpdProvider extends Provider
{

    /**
     * گرفتن آپدیت
     * 
     * @return \Mmb\Update\Upd|bool|null
     */
    public function getUpdate()
    {
        return mmb()->getUpd();
    }

    /**
     * گرفتن هندل کننده ها
     * 
     * @return array<HandlerStep|null>
     */
    public function getHandlers()
    {
        return [ ];
    }
    
}
