<?php

namespace Mds\Mmb\Tools\ATool; #auto

use Closure;
use Generator;

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
     * @param array|Generator|Callable|Closure $function
     */
    public function __construct($value)
    {
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
