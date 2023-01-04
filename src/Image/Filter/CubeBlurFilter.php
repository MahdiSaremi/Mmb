<?php

namespace Mmb\Image\Filter; #auto

class CubeBlurFilter extends Filter
{

    public $width;

    public function __construct($width)
    {
        $this->width = $width;
    }

    public function apply()
    {
        $mid = round($this->width / 2) - 1;
        $this->mapChunks($this->width, $this->width, function ($x, $y) use ($mid) {

            return $this->bitmap->getPixel($x + $mid, $y + $mid) ?:
                    $this->bitmap->getPixel($x, $y);

        });
    }

}
