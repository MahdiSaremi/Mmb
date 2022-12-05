<?php

namespace Mds\Mmb\Tools; #auto

class Optional
{

    public function __get($name)
    {
        return null;
    }

    public function __call($name, $arguments)
    {
        return null;
    }

}
