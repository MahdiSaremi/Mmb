<?php

namespace Mmb\Controller\Form; #auto

use Mmb\Controller\Controller;
use Mmb\Controller\StepHandler\Handlable;

class FormStarter extends Controller
{

    /**
     * زمان اجرای کنترلر اجرا می شود و فرم را با خود اجرا می کند
     * @param string $class
     * @return Handlable|null
     */
    public function start($class)
    {
        if(is_string($class) && class_exists($class) && method_exists($class, 'request'))
        {
            return $class::request();
        }
    }

}
