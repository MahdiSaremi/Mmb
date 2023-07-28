<?php

namespace Mmb\Update\Message\Data; #auto

use Mmb\Mmb;
use Mmb\MmbBase;

class Sticker extends MmbBase implements \Mmb\Update\Interfaces\IMsgDataID
{

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
     * @var Media
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
    public function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        $this->initFrom($args, [
            'file_id' => 'id',
            'file_unique_id' => 'uniqueID',
            'width' => 'width',
            'height' => 'height',
            'is_animated' => 'isAnim',
            'thumb' => fn($thumb) => $this->thumb = new Media('photo', $thumb, $this->_base),
            'emoji' => 'emoji',
            'set_name' => 'setName',
            'mask_position' => fn($mask) => $this->maskPos = new MaskPos($mask, $this->_base),
            'file_size' => 'size',
        ]);
    }

    /**
     * دانلود کردن فایل
     *
     * @param string $path مسیر دانلود
     * @return bool
     */
    function download($path)
    {
        return $this->_base->getFile($this->id)->download($path);
    }

    /**
     * دریافت اطلاعات فایل
     *
     * @return StickerSet|false
     */
    public function getFile()
    {
        return $this->_base->getFile($this->id);
    }

    /**
     * دریافت اطلاعات بسته استیکر
     *
     * @return StickerSet|false
     */
    public function getSet()
    {
        return $this->_base->getStickerSet($this->setName);
    }


	/**
	 * گرفتن آیدی پیام
	 *
	 * @return string
	 */
	function IMsgDataID()
    {
        return $this->id;
	}

}
