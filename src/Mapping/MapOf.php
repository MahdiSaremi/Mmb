<?php

namespace Mmb\Mapping; #auto

use Mmb\Tools\Type;

class MapOf extends Type
{

    private $type;

    /**
     * @param string|Type $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    public function cast($array)
    {
        if(!is_array($array))
            $array = [];

        foreach ($array as $key => $value)
            $array[$key] = cast($value, $this->type);

        return new Map($array);
    }
    
}
