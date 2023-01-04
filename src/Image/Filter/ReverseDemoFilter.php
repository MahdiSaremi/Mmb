<?php

namespace Mmb\Image\Filter; #auto

use Mmb\Image\Color;

class ReverseDemoFilter extends Filter
{

    public function apply()
    {
        $this->mapPixels(function (Color $color) {
            
            $color->reverse();
            return $color;

        });
    }
    
}
