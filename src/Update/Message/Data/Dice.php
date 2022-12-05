<?php

namespace Mds\Mmb\Update\Message\Data; #auto

use Mds\Mmb\Mmb;
use Mds\Mmb\MmbBase;

class Dice extends MmbBase
{

    /**
     * اموجی
     *
     * @var string
     */
    public $emoji;
    /**
     * مفدار
     *
     * @var int
     */
    public $val;
    /**
     * @var Mmb
     */
    private $_base;
    public function __construct($data, $base)
    {
        $this->_base = $base;
        $this->emoji = $data['emoji'];
        $this->val = $data['value'];
    }

}
