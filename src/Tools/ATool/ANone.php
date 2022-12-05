<?php

namespace Mmb\Tools\ATool; #auto

class ANone extends Base
{
    
    /**
     * این ایزار برای زمانیست که نمی خواهید در این ایندکس مقداری قرار بگیرد
     * 
     * * Example: `ATool::parse([0, 1, $num >= 2 ? 2 : new ANone]);`
     * 
     */
    public function __construct() { }

    public function parse(&$array, $assoc = false) { }
    
}
