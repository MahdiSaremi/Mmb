<?php

namespace Mmb\Update\Message\Data; #auto

use Mmb\Mmb;
use Mmb\MmbBase;

class Sticker extends MmbBase implements \Mmb\Update\Interfaces\IMsgDataID
{

    /**
     * @var Mmb
     */
    private $_base;

    /**
     * شناسه فایل
     *
     * @var string
     */
    public $id;
    /**
     * شناسه یکتای فایل
     *
     * @var string
     */
    public $uniqueID;
    /**
     * عرض
     *
     * @var int
     */
    public $width;
    /**
     * اموجی استیکر
     *
     * @var int
     */
    public $emoji;
    /**
     * ارتفاع
     *
     * @var int
     */
    public $height;
    /**
     * آیا متحرک است
     *
     * @var bool
     */
    public $isAnim;
    /**
     * تصویر کوچک
     *
     * @var MsgData
     */
    public $thumb;
    /**
     * نام بسته استیکر
     *
     * @var string
     */
    public $setName;
    /**
     * موقعیت ماسک
     *
     * @var Media
     */
    public $maskPos;
    /**
     * حجم فایل به بایت
     *
     * @var int
     */
    public $size;
    public function __construct($st, $base){
        $this->_base = $base;
        $this->id = $st['file_id'];
        $this->uniqueID = $st['file_unique_id'];
        $this->width = $st['width'];
        $this->height = $st['height'];
        $this->isAnim = $st['is_animated'] ?? false;
        if(isset($st['thumb']))
            $this->thumb = new Media("photo", $st['thumb'], $base);
        $this->emoji = @$st['emoji'];
        $this->setName = @$st['set_name'];
        if(isset($st['mask_position']))
            $this->maskPos = new MaskPos(@$st['mask_position'], $base);
        $this->size = @$st['file_size'];
    }

    /**
     * دانلود کردن فایل
     *
     * @param string $path مسیر دانلود
     * @return bool
     */
    function download($path){
        return $this->_base->getFile($this->id)->download($path);
    }

    /**
     * دریافت اطلاعات فایل
     *
     * @return StickerSet|false
     */
    public function getFile(){
        return $this->_base->getFile($this->id);
    }

    /**
     * دریافت اطلاعات بسته استیکر
     *
     * @return StickerSet|false
     */
    public function getSet(){
        return $this->_base->getStickerSet($this->setName);
    }


	/**
	 * گرفتن آیدی پیام
	 *
	 * @return int
	 */
	function IMsgDataID() {
        
        return $this->id;

	}

}
