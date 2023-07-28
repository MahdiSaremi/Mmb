<?php

namespace Mmb\Update\Message\Data; #auto

use Mmb\Mmb;
use Mmb\MmbBase;

class MaskPos extends MmbBase
{

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
    public function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        $this->initFrom($args, [
            'point' => 'point',
            'x_shift' => 'x',
            'y_shift' => 'y',
            'scale' => 'scale',
        ]);
    }
}
