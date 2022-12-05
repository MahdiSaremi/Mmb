<?php

namespace Mds\Mmb\Update\Message\Data; #auto

use Mds\Mmb\Mmb;
use Mds\Mmb\MmbBase;

class MaskPos extends MmbBase
{

    /**
     * @var Mmb
     */
    private $_base;

    /**
     * Point
     * موقعیت
     *
     * @var string
     */
    public $point;
    public const POINT_FOREHEAD = 'forehead';
    public const POINT_EYES = 'eyes';
    public const POINT_MOUTH = 'mouth';
    public const POINT_CHIN = 'chin';
    /**
     * موقعیت ایکس
     *
     * @var double
     */
    public $x;
    /**
     * موقعیت وای
     *
     * @var double
     */
    public $y;
    /**
     * ضریب ابعاد
     *
     * @var double
     */
    public $scale;
    public function __construct($a, $base)
    {
        $this->_base = $base;
        $this->point = $a['point'];
        $this->x = $a['x_shift'];
        $this->y = $a['y_shift'];
        $this->scale = $a['scale'];
    }
}
