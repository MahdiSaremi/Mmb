<?php
#auto-name
namespace Mmb\Calling;

use Closure;

class PropertyMemory extends DynProperty
{

    public $value;

    /**
     * ایجاد پراپرتی جدید با حافظه
     *
     * @param Closure|null $get `fn(&$memory) => $memory`
     * @param Closure|null $set `fn(&$memory, $value) => $memory = $value`
     */
    public function __construct(
        private ?Closure $get = null,
        private ?Closure $set = null,
        $default = null
    )
    {
        $this->value = $default;
    }
    
    public function get() : mixed
    {
        if($get = $this->get)
        {
            return $get($this->value, $this);
        }

        return $this->value;
    }

    public function set($value) : void
    {
        if($set = $this->set)
        {
            $set($this->value, $value);
            return;
        }

        $this->value = $value;
    }
    
}
