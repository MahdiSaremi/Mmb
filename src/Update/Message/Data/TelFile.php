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
    function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        $this->initFrom($args, [
            'file_id' => 'id',
            'file_path' => 'path',
            'file_size' => 'size',
            'unique_id' => 'uniqueID',
        ]);
    }
    
    /**
     * دانلود فایل
     *
     * @param string $path محل قرار گیری فایل
     * @return bool
     */
    function download($path)
    {
        return $this->_base->copyByFilePath($this->path, $path);
    }
    
}
