<?php
#auto-name
namespace Mmb\Calling;

use Closure;

class Property extends DynProperty
{

    /**
     * ایجاد پراپرتی جدید
     *
     * @param Closure|null $get `fn() => $this->myvalue`
     * @param Closure|null $set `fn($value) => $this->myvalue = $value`
     */
    public function __construct(
        private ?Closure $get = null,
        private ?Closure $set = null
    )
    {
    }

    public function get() : mixed
    {
        if($get = $this->get)
        {
            return $get();
        }

        return parent::get();
    }

    public function set($value) : void
    {
        if($set = $this->set)
        {
            $set($value);
            return;
        }

        parent::set($value);
    }
    
}
