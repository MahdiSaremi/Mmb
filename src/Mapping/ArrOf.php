<?php

namespace Mmb\Mapping; #auto

use Mmb\Tools\Type;

class ArrOf extends Type
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

        foreach ($array as $index => $value)
            $array[$index] = cast($value, $this->type);

        return new Arr($array);
    }
    
}
