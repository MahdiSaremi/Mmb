<?php

namespace Mmb\Update\Chat; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\Message\Data\Media;

class ChatPhoto extends MmbBase
{
    /**
     * تصویر کوچک (160 * 160)
     *
     * @var Media|null
     */
    public $small;
    /**
     * تصویر بزرگ (640 * 640)
     *
     * @var Media|null
     */
    public $big;

   public function __construct(array $args, ?Mmb $mmb = null)
   {
        parent::__construct($args, $mmb);

        if(isset($args['small_file_id']))
        {
            $this->small = new Media("photo", [
                'file_id' => $args['small_file_id'],
                'width' => 160,
                'height' => 160
            ], $this->_base);
        }
        
        if(isset($args['big_file_id']))
        {
            $this->big = new Media("photo", [
                'file_id' => $args['big_file_id'],
                'width' => 640,
                'height' => 640
            ], $this->_base);
        }
    }
}
