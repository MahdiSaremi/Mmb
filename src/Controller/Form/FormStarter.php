<?php

namespace Mmb\Controller\Form; #auto

use Mmb\Controller\Controller;

class FormStarter extends Controller
{

    public function start($class)
    {
        if(is_string($class) && class_exists($class) && method_exists($class, 'request'))
        {
            return $class::request();
        }
    }

}
