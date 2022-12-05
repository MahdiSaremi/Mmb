<?php

namespace Mds\Mmb\Controller\StepHandler; #auto

interface Handlable
{

    /**
     * @return StepHandler
     */
    public function getHandler();
    
}
