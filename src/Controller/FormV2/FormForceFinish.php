<?php
#auto-name
namespace Mmb\Controller\FormV2;

use Exception;

class FormForceFinish extends Exception
{
    
    public function __construct(
        public $errorMessage = null
    )
    {
        parent::__construct("Form stop/error not handled! You should not use stop/error function in form queries");
    }
    
}
