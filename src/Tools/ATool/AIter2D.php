<?php

namespace Mmb\Tools\ATool; #auto

use Closure;
use Generator;
use Mmb\Mapping\Arrayable;
use Mmb\Tools\ATool;

class AIter2D extends Base
{
    
    private $value;
    private $cols;
    /**
     * این ابزار برای افزودن یک جنراتور به آرایه ست
     * 
     * روش های تعریف:
     * * 1: `function() { yield 1; yield 2; ... }`
     * * 2: `[1, 2, ...]`
     *
     * @param array|Generator|callable|Closure $value
     * @param int $colCount
     */
    public function __construct($value, $colCount)
    {
        if($value instanceof Arrayable)
        {
            $value = $value->toArray();
        }

        $this->value = $value;
        $this->cols = $colCount;
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
                    $ar = [];
                    foreach($v as $value) {
                        $ar[] = $value;
                    }
                    array_push($array, ...ATool::make2D($ar, $this->cols));
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
                array_push($array, ...ATool::make2D($this->value, $this->cols));
            }
        }
    }
}
