<?php

namespace Mmb\Update\Message\Data; #auto

use Mmb\MmbBase;

class StickerSet extends MmbBase
{

    /**
     * @var MMB
     */
    private $_base;

    /**
     * نام
     *
     * @var string
     */
    public $name;
    /**
     * عنوان
     *
     * @var string
     */
    public $title;
    /**
     * آیا استیکر متحرک دارد
     *
     * @var bool
     */
    public $hasAnim;
    /**
     * آیا استیکر ماسک دارد
     *
     * @var bool
     */
    public $hasMask;
    /**
     * استیکر ها
     *
     * @var Sticker[]
     */
    public $stickers;
    /**
     * عکس کوچک
     *
     * @var Media
     */
    public $thumb;
    function __construct($a, $base)
    {
        $this->_base = $base;
        $this->name = $a['name'];
        $this->title = $a['title'];
        $this->hasAnim = $a['is_animated'];
        $this->hasMask = $a['contains_masks'];
        $this->stickers = [];
        foreach($a['stickers'] as $once)
            $this->stickers[] = new Sticker($once, $base);
        if(isset($a['thumb']))
            $this->thumb = new Media("photo", $a['thumb'], $base);
    }
}
