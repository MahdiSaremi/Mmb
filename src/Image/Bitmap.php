<?php

namespace Mmb\Image; #auto

use Mmb\Exceptions\MmbException;
use Mmb\Exceptions\TypeException;
use Mmb\Image\Filter\Filter;

/**
 * @property int $width طول تصویر
 * @property int $height ارتفاع تصویر
 */
class Bitmap
{

    /**
     * @var resource|mixed
     */
    private $image;
    
    /**
     * ایجاد تصویر از طریق جی دی
     * 
     * @param resource $image
     */
    public function __construct($image)
    {
        self::supportCheck();

        $this->image = $image;
    }
    
    /**
     * بررسی پشتیبانی کردن سرور
     * 
     * @throws MmbException 
     * @return void
     */
    private static function supportCheck()
    {
        if(!function_exists('imagepng'))
        {
            throw new MmbException("Bitmap required 'gd' extension");
        }
    }

    /**
     * لود کردن از طریق فایل
     * 
     * @param string $path
     * @param string $format
     * @throws MmbException 
     * @return static|false
     */
    public static function loadFromFile($path, $format = null)
    {
        self::supportCheck();

        if($format === null)
        {
            $format = pathinfo($path, PATHINFO_EXTENSION) ?: 'png';
        }

        $func = 'imageCreateFrom' . $format;
        if(function_exists($func))
        {
            if($image = $func($path))
                return new static ($image);

            return false;
        }

        throw new MmbException("Format $format is not supported");
    }

    /**
     * لود کردن از طریق رشته حاوی اطلاعات باینری تصویر
     * 
     * @param string $string
     * @return static|false
     */
    public static function loadFromString($string)
    {
        self::supportCheck();

        if($image = imageCreateFromString($string))
            return new static ($image);

        return false;
    }

    /**
     * ساخت تصویر خالی
     * 
     * @param int $width
     * @param int $height
     * @param Color|int|bool $fillColor
     * @return static|false
     */
    public static function create($width, $height, $fillColor = false)
    {
        $image = imageCreateTrueColor($width, $height);
        if (!$image)
            return false;

        $bmp = new static ($image);
        if ($fillColor !== false)
            $bmp->fillAll($fillColor);

        return $bmp;
    }


    /**
     * گرفتن رنگ پیکسل
     * 
     * @param int $x
     * @param int $y
     * @return Color|false
     */
    public function getPixel($x, $y)
    {
        if(($color = imageColorAt($this->image, $x, $y)) !== false)
            return new Color($color);

        return false;
    }

    /**
     * تنظیم رنگ پیکسل
     * 
     * @param int $x
     * @param int $y
     * @param Color|int $color
     * @return bool
     */
    public function setPixel($x, $y, $color)
    {
        if ($color instanceof Color)
            $color = $color->getColorId();

        return imageFill($this->image, $x, $y, $color);
    }

    /**
     * پر کردن به شکل مستطیل
     * 
     * @param int $fromX
     * @param int $fromY
     * @param int $toX
     * @param int $toY
     * @param Color|int $color
     * @return bool
     */
    public function fill($fromX, $fromY, $toX, $toY, $color)
    {
        if ($color instanceof Color)
            $color = $color->getColorId();

        return imagefilledrectangle($this->image, $fromX, $fromY, $toX, $toY, $color);
    }

    /**
     * پر کردن کل تصویر با رنگ
     * 
     * @param Color|int $color
     * @return bool
     */
    public function fillAll($color)
    {
        return $this->fill(0, 0, $this->getWidth(), $this->getHeight(), $color);
    }

    /**
     * اعمال کردن فیلتر
     * 
     * @param Filter $filter
     * @return void
     */
    public function apply(Filter $filter)
    {
        $filter->bitmap = $this;
        $filter->apply();
    }
    
    /**
     * گرفتن طول
     * 
     * @return int
     */
    public function getWidth()
    {
        return imagesx($this->image);
    }

    /**
     * گرفتن ارتفاع
     * 
     * @return int
     */
    public function getHeight()
    {
        return imagesy($this->image);
    }


    public function __get($name)
    {
        switch($name)
        {
            case 'width':
                return $this->getWidth();
            case 'height':
                return $this->getHeight();
        }

        error_log("Undefined property '$name'");
    }

    public function save($path, $format = null)
    {
        if($format === null)
        {
            $format = pathinfo($path, PATHINFO_EXTENSION) ?: 'png';
        }

        $func = 'image' . $format;
        if(function_exists($func))
        {
            return $func($this->image, $path);
        }

        throw new MmbException("Format $format is not supported");
    }

}
