<?php

namespace Mmb\Image; 
use Mmb\Exceptions\MmbException;#auto

/**
 * @property int $r مقدار قرمز - از 0 تا 255
 * @property int $g مقدار سبز - از 0 تا 255
 * @property int $b مقدار آبی - از 0 تا 255
 * @property float $a مقدار آلفا - بین 0 و 1
 */
class Color
{

    /**
     * @var int
     */
    private $color;
    
    public function __construct(int $color)
    {
        $this->color = $color;
    }

    /**
     * ایجاد رنگ توسط سه رنگ قرمز - سبز - آبی و مقدار شفافیت
     *
     * @param int $r قرمز - از 0 تا 255
     * @param int $g سبز - از 0 تا 255
     * @param int $b آبی - از 0 تا 255
     * @param float|bool $a آلفا - بین 0 و 1
     * @return static
     */
    public static function rgb($r, $g, $b, $a = false)
    {
        $color = ($b % 256);
        $color += ($g % 256) * 256;
        $color += ($r % 256) * 65536;
        if($a !== false)
        {
            $a = 127 - (($a * 127) % 128); // Scale 0-1 to 0-127, and reverse
            $color += $a * 16777216;
        }

        return new static($color);
    }

    /**
     * ایجاد رنگ توسط کد هگزادسیمال
     *
     * @param string $hex
     * @return static|false
     */
    public static function hex($hex)
    {
        $hex = ltrim($hex, '#');
        $r = 0;
        $g = 0;
        $b = 0;
        $a = false;
        switch(strlen($hex))
        {
            case 0:
                break;
            case 1:
                $r = $g = $b = @hexdec($hex.$hex);
                break;
            case 2:
                $r = $g = $b = @hexdec($hex);
                break;
            case 3:
                $r = @hexdec($hex[0] . $hex[0]);
                $g = @hexdec($hex[1] . $hex[1]);
                $b = @hexdec($hex[2] . $hex[2]);
                break;
            case 4:
                $r = @hexdec($hex[0] . $hex[0]);
                $g = @hexdec($hex[1] . $hex[1]);
                $b = @hexdec($hex[2] . $hex[2]);
                $a = @hexdec($hex[3] . $hex[3]) / 255;
                break;
            case 6:
                $r = @hexdec($hex[0] . $hex[1]);
                $g = @hexdec($hex[2] . $hex[3]);
                $b = @hexdec($hex[4] . $hex[5]);
                break;
            case 8:
                $r = @hexdec($hex[0] . $hex[1]);
                $g = @hexdec($hex[2] . $hex[3]);
                $b = @hexdec($hex[4] . $hex[5]);
                $a = @hexdec($hex[6] . $hex[7]) / 255;
                break;
            default:
                return false;
        }

        return static::rgb($r, $g, $b, $a);
    }


    /**
     * گرفتن کد رنگ
     * 
     * @return int
     */
    public function getColorId()
    {
        if($this->editedRGBA)
        {
            $color = ($this->_rgba[2] % 256);
            $color += ($this->_rgba[1] % 256) * 256;
            $color += ($this->_rgba[0] % 256) * 65536;
            
            $a = 127 - (($this->_rgba[3] * 127) % 128); // Scale 0-1 to 0-127, and reverse
            $color += $a * 16777216;
            $this->color = $color;
            $this->editedRGBA = false;

            return $color;
        }

        return $this->color;
    }



    private $_rgba = false;
    private $editedRGBA = false;
    /**
     * استخراج مقدار رنگ قرمز - سبز - آبی - آلفا از کد رنگ
     * 
     * @return array
     */
    public function getRGBA()
    {
        if($this->_rgba === false)
        {
            $col = $this->color;
            $this->_rgba = [];

            $r = $col % 16777216;
            $alpha = ($col - $r) / 16777216;
            $alpha = (127 - $alpha) / 127;

            $col = $r;
            $r = $col % 65536;
            $this->_rgba[0] = ($col - $r) / 65536;

            $col = $r;
            $r = $col % 256;
            $this->_rgba[1] = ($col - $r) / 256;

            $this->_rgba[2] = $r;
            $this->_rgba[3] = $alpha;
        }

        return $this->_rgba;
    }

    public function __get($name)
    {
        switch($name)
        {
            case 'r':
                return $this->getRGBA()[0];
            case 'g':
                return $this->getRGBA()[1];
            case 'b':
                return $this->getRGBA()[2];
            case 'a':
                return $this->getRGBA()[3];
        }

        error_log("Undefined property '$name'");
    }

    public function __set($name, $value)
    {
        switch($name)
        {
            case 'r':
                $this->getRGBA()[0] = $value;
                $this->editedRGBA = true;
                break;

            case 'g':
                $this->getRGBA()[1] = $value;
                $this->editedRGBA = true;
                break;

            case 'b':
                $this->getRGBA()[2] = $value;
                $this->editedRGBA = true;
                break;

            case 'a':
                $this->getRGBA()[3] = $value;
                $this->editedRGBA = true;
                break;

            default:
                $this->$name = $value;
        }
    }
    
    public function clone()
    {
        return new static ($this->getColorId());
    }
    
    /**
     * معکوس کردن رنگ ها
     * 
     * @return void
     */
    public function reverse()
    {
        $this->r = 255 - $this->r;
        $this->g = 255 - $this->g;
        $this->b = 255 - $this->b;
    }

    /**
     * سیاه سفید کردن رنگ
     * 
     * @return void
     */
    public function blackWhite()
    {
        $this->r = $this->g = $this->b =
            ($this->r + $this->g + $this->b) / 3;
    }
    
}
