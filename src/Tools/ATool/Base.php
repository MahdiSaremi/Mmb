<?php

namespace Mmb\Tools\ATool; 
use Mmb\Mapping\Arrayable;#auto

/**
 * این کلاس، کلاس ابزاریست که در کلاس آتول می توانید استفاده کنید
 */
abstract class Base implements Arrayable
{
    public abstract function parse(&$array, $assoc = false);

    /**
     * گرفتن مقدار بصورت آرایه
     *
     * @return array
     */
    public function get($assoc = false)
    {
        $array = [];
        $this->parse($array, $assoc);
        return $array;
    }

    /**
     * افزودن مقدار بین دو آرایه
     *
     * @param array $before
     * @param array $after
     * @param boolean $assoc
     * @return array
     */
    public function between(array $before, array $after, $assoc = false)
    {
        $this->parse($before, $assoc);
        if($assoc)
        {
            return array_replace($before, $after);
        }
        else
        {
            array_push($before, ...$after);
            return $before;
        }
    }

    public function toArray()
    {
        return $this->get();
    }
}
