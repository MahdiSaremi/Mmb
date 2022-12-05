<?php

namespace Mmb\Kernel; #auto

use Mmb\Tools\ATool;
use Mmb\Tools\Staticable;

class Config 
{
    use Staticable;

    private $configs = [];
    
    public function set($name, $value)
    {
        ATool::selectorSet($this->configs, $name, $value);
    }

    public function get($name, $default = null)
    {
        return ATool::selectorGet($this->configs, $name, $default);
    }

    public function unset($name)
    {
        ATool::selectorUnset($this->configs, $name);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function applyFile($file, $name)
    {
        $this->set($name, includeFile($file));
    }
    
}
