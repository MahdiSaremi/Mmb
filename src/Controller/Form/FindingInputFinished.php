<?php

namespace Mmb\Controller\Form; #auto

use Mmb\Controller\StepHandler\Handlable;

class FindingInputFinished extends \Exception
{

    /**
     * @var Handlable|null
     */
    public $result;
    
    public function __construct($result)
    {
        $this->result = $result;
    }

}
