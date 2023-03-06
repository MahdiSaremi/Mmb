<?php
#auto-name
namespace Mmb\IO;

use Mmb\Files\Files;

class File extends Stream
{

    protected $path;
    protected $stream;
    
    public function __construct($path, $mode)
    {
        $this->path = $path;
        $this->stream = @fopen($path, $mode);
        if ($this->stream === false)
            throw new FileException("Failed to open file '$path'");
    }

    protected function getStream()
    {
        if(!$this->stream)
            throw new FileException("File was closed");
        return $this->stream;
    }

    protected $locked;

    /**
     * قفل کردن فایل
     * 
     * اگر فایلی در همین لحظه قفل شده باشد، به اندازه ی مکس ترای، منتظر باز شدن آن می ماند
     * 
     * @param int $maxTry
     * @return void
     */
    public function lock($maxTry = 10000)
    {
        Files::lock($this->getStream(), $maxTry);
        $this->locked = true;
    }

    /**
     * باز کردن قفل فایل
     * 
     * @return void
     */
    public function unlock()
    {
        Files::unlock($this->getStream());
        $this->locked = false;
    }

    /**
     * بستن فایل
     * 
     * @return void
     */
    public function close()
    {
        fclose($this->getStream());
        $this->stream = null;
    }
    
    /**
     * بستن خودکار فایل
     */
    public function __destruct()
    {
        if($this->stream)
        {
            if ($this->locked)
                $this->unlock();

            $this->close();
        }
    }

    /**
     * خواندن بایت بایت
     * 
     * @param int $length
     * @return bool|string
     */
    public function read($length = 1)
    {
        return @fread($this->getStream(), $length);
    }

    /**
     * خواندن کل فایل
     * 
     * @return bool|string
     */
    public function readAll()
    {
        return stream_get_contents($this->getStream());
    }

    /**
     * خواندن کاراکتر از فایل
     * 
     * @return bool|string
     */
    public function readChar()
    {
        return fgetc($this->getStream());
    }

    /**
     * خواندن یک خط از فایل
     * 
     * @return bool|string
     */
    public function readLine()
    {
        return fgets($this->getStream());
    }

    /**
     * تنظیم موقعیت اشاره گر
     * 
     * @param int $offset
     * @param int $whence
     * @return void
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        fseek($this->getStream(), $offset, $whence);
    }

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
    public function write($string)
    {
        fwrite($this->getStream(), $string);
    }

    /**
     * بررسی می کند فایل به پایان رسیده است یا خیر
     * 
     * @return bool
     */
    public function eof()
    {
        return feof($this->getStream());
    }

}
