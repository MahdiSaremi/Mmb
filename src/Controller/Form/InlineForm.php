<?php

namespace Mmb\Controller\Form; #auto

use Mmb\Listeners\Listeners;

abstract class InlineForm extends Form
{

    public function onFinish()
    {
        $method = $this->_callback;
        return Listeners::invokeMethod2($method, [ $this ]);
    }

    public function onCancel()
    {
        $method = $this->_callback_cancel;
        return Listeners::invokeMethod2($method, [ $this ]);
    }

    public static function requestInline(array $callbackMethod, array $cancelMethod, array $inputs = [])
    {
        $inputs['_callback'] = $callbackMethod;
        $inputs['_callback_cancel'] = $cancelMethod;
        return static::request($inputs);
    }
    
}
