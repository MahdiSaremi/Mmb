<?php
#auto-name
namespace Mmb\Calling;

use ValueError;

class DynProperty
{

    public function get() : mixed
    {
        throw new ValueError("Can't get this property");
    }

    public function set($value) : void
    {
        throw new ValueError("Can't set this property");
    }
    
}
