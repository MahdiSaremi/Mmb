<?php

namespace Mmb\Update\Message\Data; #auto

use Mmb\Mmb;
use Mmb\MmbBase;

class StickerSet extends MmbBase
{

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
    function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        $this->initFrom($args, [
            'name' => 'name',
            'title' => 'title',
            'is_animated' => 'hasAnim',
            'contains_masks' => 'hasMask',
            'stickers' => fn($stickers) => $this->stickers = array_map(fn($sticker) => new Sticker($sticker, $this->_base), $stickers),
            'thumb' => fn($thumb) => $this->thumb = new Media('photo', $thumb, $this->_base),
        ]);
    }
}
