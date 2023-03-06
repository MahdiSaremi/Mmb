<?php
#auto-name
namespace Mmb\IO;

class StringStream extends Stream
{

    protected $string;
    protected $start;
    protected $end;
    public function __construct($string, $offset = 0, $length = null)
    {
        $this->string = $string;
        $this->start = $offset;
        $this->end = $length === null ? strlen($string) : $offset + $length;
    }

    protected $pointer = 0;

    /**
     * خواندن بایت بایت
     * 
     * @param int $length
     * @return bool|string
     */
    public function read($length = 1)
    {
        if($length == 1)
        {
            if($this->eof())
                return false;
            return $this->string[$this->pointer++];
        }
        else
        {
            $res = substr($this->string, min($this->pointer, $this->end), min($length, $this->end - $this->pointer));
            if ($res === "")
                return false;
            $this->pointer += $length;
            return $res;
        }
    }

    /**
     * خواندن کل فایل
     * 
     * @return bool|string
     */
    public function readAll()
    {
        return $this->string;
    }

    /**
     * خواندن کاراکتر از فایل
     * 
     * @return bool|string
     */
    public function readChar()
    {
        return $this->read();
    }

    /**
     * خواندن یک خط از فایل
     * 
     * @return bool|string
     */
    public function readLine()
    {
        $res = "";
        while((($char = $this->read()) !== false) && $char != "\n")
            $res .= $char;
        return $res;
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
        switch($whence)
        {
            case SEEK_SET:
                $this->pointer = $this->start + $offset;
                break;
            case SEEK_END:
                $this->pointer = $this->end + $offset;
                break;
            case SEEK_CUR:
                $this->pointer = max($this->start, $this->pointer + $offset);
                break;
        }
    }

    /**
     * نوشتن در فایل
     * 
     * @param string $string
     * @return void
     */
    public function write($string)
    {
        $this->string = substr_replace($this->string, $string, $this->pointer, strlen($string));
        $this->pointer += strlen($string);
        if($this->pointer > $this->end)
        {
            $this->end = $this->pointer;
        }
    }

    /**
     * بررسی می کند فایل به پایان رسیده است یا خیر
     * 
     * @return bool
     */
    public function eof()
    {
        return $this->pointer >= $this->end;
    }

    

}
