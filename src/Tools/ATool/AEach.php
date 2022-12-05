<?php

namespace Mmb\Tools\ATool; #auto

class AEach extends Base
{
    private $array;
    private $callback;
    /**
     * این ایزار برای افزودن مقدار ها به این قسمت آرایست
     *
     * * اگر کالبک خالی باشد، بصورت خام آرایه قرار می گیرد
     * * callback: `function ($value [, $key])`
     * * return value: `$value` or `[$key, $value]`
     * 
     * @param array $array
     * @param callable $callback
     */
    public function __construct($array, $callback = null)
    {
        $this->array = $array;
        $this->callback = $callback;
    }

    public function parse(&$array, $assoc = false)
    {
        $callback = $this->callback;
        if($callback) {
            if($assoc) {
                foreach($this->array as $a => $b) {
                    list($key, $val) = $callback($b, $a);
                    $array[$key] = $val;
                }
            }
            else {
                foreach($this->array as $a => $b) {
                    $array[] = $callback($b, $a);
                }
            }
        }
        else {
            if($assoc) {
                foreach($this->array as $key => $value)
                    $array[$key] = $value;
            }
            else {
                array_push($array, ...$this->array);
            }
        }
    }
}
