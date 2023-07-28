<?php

namespace Mmb\Update\Message\Data; #auto

use Mmb\Mmb;
use Mmb\MmbBase;

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
    public function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        $this->initFrom($args, [
            'emoji' => 'emoji',
            'value' => 'val',
        ]);
    }

}
