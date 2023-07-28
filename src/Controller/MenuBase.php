<?php

namespace Mmb\Controller; #auto

use Mmb\Controller\StepHandler\Handlable;

abstract class MenuBase implements Handlable
{

    /**
     * گرفتن دکمه ها برای نمایش
     *
     * @return array
     */
    public abstract function getMenuKey();
    
    /**
     * گرفتن پیام منو
     *
     * @param string|null $name
     * @return string|array|null
     */
    public abstract function getMessage($name = null);

}
