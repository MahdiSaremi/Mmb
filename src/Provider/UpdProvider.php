<?php

namespace Mds\Mmb\Provider; #auto

use Mds\Mmb\Controller\Handler\HandlerStep;
use Mds\Mmb\Controller\StepHandler\StepHandler;

class UpdProvider extends Provider
{

    /**
     * گرفتن آپدیت
     * 
     * @return \Mds\Mmb\Update\Upd|bool|null
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
