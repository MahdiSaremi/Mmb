<?php

namespace Mmb\Tools\ATool; #auto

use Closure;
use Generator;
use Mmb\Mapping\Arrayable;

class AIter extends Base
{
    
    private $value;
    /**
     * این ابزار برای افزودن یک جنراتور به آرایه ست
     * 
     * روش های تعریف:
     * * 1: `function() { yield 1; yield 2; ... }`
     * * 2: `[1, 2, ...]`
     *
     * @param array|Generator|callable|Closure $function
     */
    public function __construct($value)
    {
        if($value instanceof Arrayable)
        {
            $value = $value->toArray();
        }

        $this->value = $value;
    }

    public function parse(&$array, $assoc = false)
    {
        if(is_callable($this->value) || $this->value instanceof Closure) {
            $v = $this->value;
            $v = $v();
            if($v instanceof Generator) {
                if($assoc) {
                    foreach($v as $key => $value) {
                        $array[$key] = $value;
                    }
                }
                else {
                    foreach($v as $value) {
                        $array[] = $value;
                    }
                }
            }
        }
        else {
            if($assoc) {
                foreach($this->value as $key => $value) {
                    $array[$key] = $value;
                }
            }
            else {
                foreach($this->value as $value) {
                    $array[] = $value;
                }
            }
        }
    }
}
