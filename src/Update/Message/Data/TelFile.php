<?php

namespace Mmb\Update\Message\Data; #auto

use Mmb\Mmb;
use Mmb\MmbBase;

class TelFile extends MmbBase
{
    /**
     * آیدی فایل
     *
     * @var string
     */
    public $id;
    /**
     * آدرس فایل
     * 
     * شما با لینک زیر می توانید فایل را دانلود کنید
     * https://api.telegram.org/file/bot[TOKEN]/[FILE_PATH]
     * 
     * همچنین از تابع دانلود نیز می توانید استفاده کنید
     * `$myFile->download("temps/test.txt");`
     * 
     * @var string
     */
    public $path;
    /**
     * حجم فایل
     *
     * @var int
     */
    public $size;
    /**
     * آیدی یکتای فایل
     *
     * @var int
     */
    public $uniqueID;
    /**
     * @var Mmb
     */
    private $_base;
    function __construct($f, $base){
        $this->_base = $base;
        $this->id = $f['file_id'];
        $this->path = $f['file_path'];
        $this->size = $f['file_size'];
        $this->uniqueID = $f['unique_id'];
    }
    
    /**
     * دانلود فایل
     *
     * @param string $path محل قرار گیری فایل
     * @return bool
     */
    function download($path){
        return $this->_base->copyByFilePath($this->path, $path);
    }
    
}
