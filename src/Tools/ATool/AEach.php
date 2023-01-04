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
     * * return value: `$value` or `[$key, $value]` for assoc
     * * yield value: `$value` or `$key => $value` for assoc
     * 
     * می توانید از دو روش ریترن و یلد استفاده کنید
     * 
     * `$nums = aParse([ aEach(range(1,3), function($num) { return $num + 0.5; }) ]); // [1.5, 2.5, 3.5]`
     * `$nums = aParse([ aEach(range(1,3), function($num) { yield $num; yield $num + 0.5; }) ]); // [1, 1.5, 2, 2.5, 3, 3.5]`
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
        if($callback)
        {
            if($assoc)
            {
                foreach($this->array as $a => $b)
                {
                    $callbackValue = $callback($b, $a);
                    if($callbackValue instanceof \Generator)
                    {
                        foreach ($callbackValue as $key => $singleValue)
                            $array[$key] = $singleValue;
                    }
                    else
                    {
                        list($key, $val) = $callbackValue;
                        $array[$key] = $val;
                    }
                }
            }
            else
            {
                foreach($this->array as $a => $b)
                {
                    $callbackValue = $callback($b, $a);
                    if($callbackValue instanceof \Generator)
                    {
                        foreach ($callbackValue as $singleValue)
                            $array[] = $singleValue;
                    }
                    else
                    {
                        $array[] = $callbackValue;
                    }
                }
            }
        }
        else
        {
            if($assoc)
            {
                foreach($this->array as $key => $value)
                    $array[$key] = $value;
            }
            else
            {
                array_push($array, ...$this->array);
            }
        }
    }
}
