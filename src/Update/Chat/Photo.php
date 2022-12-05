<?php

namespace Mmb\Update\Chat; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\Message\Data\Media;

class Photo extends MmbBase
{
    /**
     * تصویر کوچک (160 * 160)
     *
     * @var Media
     */
    public $small;
    /**
     * تصویر بزرگ (640 * 640)
     *
     * @var Media
     */
    public $big;
    /**
     * @var Mmb
     */
    private $_base;
   public function __construct($v, $base)
   {
        $this->_base = $base;

        $this->small = new Media("photo", [
            'file_id' => $v['small_file_id'],
            'width' => 160,
            'height' => 160
        ], $base);
        
        $this->big = new Media("photo", [
            'file_id' => $v['big_file_id'],
            'width' => 640,
            'height' => 640
        ], $base);
    }
}
