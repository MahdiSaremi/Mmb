<?php
#auto-name
namespace Mmb\Controller\FormV2;

use Mmb\Calling\Caller;

abstract class Form2Callback extends Form2
{

    public static function requestCallback(array|string $onFinish, array|string $onCancel, array $datas = [])
    {
        $datas['#f'] = $onFinish;
        $datas['#c'] = $onCancel;
        static::request($datas);
    }

    public function onCancel()
    {
        if($callback = $this->get('#c'))
        {
            return Caller::invoke2($callback);
        }
    }

    public function onFinish()
    {
        if($callback = $this->get('#f'))
        {
            return Caller::invoke2($callback);
        }
    }
    
}
