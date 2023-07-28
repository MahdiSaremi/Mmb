<?php

namespace Mmb\Controller; #auto

class MenuSubInline extends MenuBase
{

    public $menu;
    public $name;

    public function __construct(Menu $parent, $name)
    {
        $this->menu = $parent;
        $this->name = $name;
    }

    /**
     * گرفتن دکمه ها برای نمایش
     *
     * @return array
     */
    public function getMenuKey()
    {
        return $this->menu->getKey($this->name);
    }

    public function getHandler()
    {
        return $this->menu->getHandler();
    }
    
    /**
     * گرفتن پیام منو
     *
     * @param string|null $name
     * @return string|array|null
     */
    public function getMessage($name = null)
    {
        return $this->menu->getMessage($name ?: $this->name);
    }

}
