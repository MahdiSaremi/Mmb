<?php

namespace Mds\Mmb\Tools\ATool; #auto

class AIf extends Base
{
    
    private $condision;
    private $value;
    /**
     * با این ابزار می توانید یک مقدار را در صورت صحیح بودن شرط قرار دهید
     *
     * @param bool|mixed $condision
     * @param mixed $value
     */
    public function __construct($condision, $value)
    {
        $this->condision = $condision ? true : false;
        $this->value = $value;
    }

    public function parse(&$array, $assoc = false)
    {
        if($this->condision) {
            $array[] = $this->value;
        }
    }
}
