<?php

namespace Mmb\Image\Filter; #auto

use Mmb\Image\Bitmap;

abstract class Filter
{

    /**
     * @var Bitmap
     */
    public $bitmap;

    public abstract function apply();

    /**
     * مپ کردن پیکسل ها
     * 
     * @param \Closure $callback
     * @return void
     */
    public function mapPixels($callback)
    {
        $width = $this->bitmap->width;
        $height = $this->bitmap->height;
        for($x = 0; $x < $width; $x++)
        {
            for($y = 0; $y < $height; $y++)
            {
                $this->bitmap->setPixel($x, $y, 
                    $callback($this->bitmap->getPixel($x, $y))
                );
            }
        }
    }

    /**
     * مپ کردن پیکسل ها
     * 
     * @param int $width
     * @param int $height
     * @param \Closure $callback
     * @return void
     */
    public function mapChunks($width, $height, $callback)
    {
        $width = $this->bitmap->width;
        $height = $this->bitmap->height;
        for($x = 0; $x < $width; $x += $width)
        {
            for($y = 0; $y < $height; $y += $height)
            {
                $this->bitmap->fill($x, $y, $x + $width, $y + $height,
                    $callback($x, $y)
                );
            }
        }
    }

    /**
     * گرفتن تمامی پیکسل ها
     * 
     * @return Pixels
     */
    public function pixels()
    {
        return new Pixels($this->bitmap);
    }
    
}
