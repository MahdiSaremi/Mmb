<?php
#auto-name
namespace Mmb\Controller\FormV2;
use Exception;

class InputForceFinish extends Exception
{

    public function __construct(
        public $errorMessage = null
    )
    {
        parent::__construct("Input error/finish not handled! You should not use error/finish function in input queries");
    }
    
}
