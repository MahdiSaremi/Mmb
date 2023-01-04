<?php

namespace Mmb\Image\Filter; #auto

use Mmb\Image\Bitmap;
use Mmb\Image\Color;

class Pixels
{

    /**
     * پیکسل ها
     * 
     * @var array<array<Color>>
     */
    public $all = [];

    public $image;

    public function __construct(Bitmap $image)
    {
        $this->image = $image;
        $width = $image->width;
        $height = $image->height;
        for($x = 0; $x < $width; $x++)
        {
            for($y = 0; $y < $height; $y++)
            {
                @$this->all[$x][$y] = $image->getPixel($x, $y);
            }
        }
    }

    public function apply()
    {
        $image = $this->image;
        $width = $image->width;
        $height = $image->height;
        for($x = 0; $x < $width; $x++)
        {
            for($y = 0; $y < $height; $y++)
            {
                $image->setPixel($x, $y, $this->all[$x][$y]);
            }
        }
    }

    public function set($x, $y, $color)
    {
        $this->all[$x][$y] = $color;
    }

    /**
     * گرفتن پیکسل
     * 
     * @param int $x
     * @param int $y
     * @return Color|mixed
     */
    public function get($x, $y)
    {
        return $this->all[$x][$y];
    }
    
}
