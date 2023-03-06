<?php
#auto-name
namespace Mmb\IO;

abstract class Stream
{
    use BinaryIOTrait;

    /**
     * خواندن بایت بایت
     * 
     * @param int $length
     * @return bool|string
     */
    public abstract function read($length = 1);

    /**
     * خواندن کل فایل
     * 
     * @return bool|string
     */
    public abstract function readAll();

    /**
     * خواندن کاراکتر از فایل
     * 
     * @return bool|string
     */
    public abstract function readChar();

    /**
     * خواندن یک خط از فایل
     * 
     * @return bool|string
     */
    public abstract function readLine();

    /**
     * تنظیم موقعیت اشاره گر
     * 
     * @param int $offset
     * @param int $whence
     * @return void
     */
    public abstract function seek($offset, $whence = SEEK_SET);

    /**
     * افزودن به موقعیت اشاره گر
     * 
     * @param int $offset
     * @return void
     */
    public function seekAdd($offset)
    {
        $this->seek($offset, SEEK_CUR);
    }

    /**
     * تنظیم اشاره گر به انتهای فایل
     * 
     * @param int $offset
     * @return void
     */
    public function seekEnd($offset = 0)
    {
        $this->seek($offset, SEEK_END);
    }

    /**
     * تنظیم اشاره گر به ابتدای فایل
     * 
     * @param int $offset
     * @return void
     */
    public function seekFirst($offset = 0)
    {
        $this->seek($offset);
    }

    /**
     * نوشتن در فایل
     * 
     * @param string $string
     * @return void
     */
    public abstract function write($string);

    /**
     * بررسی می کند فایل به پایان رسیده است یا خیر
     * 
     * @return bool
     */
    public abstract function eof();

}
