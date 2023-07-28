<?php

namespace Mmb\Update\Message\Data; #auto

use Mmb\Mmb;
use Mmb\MmbBase;

class Location extends MmbBase 
{

    public $longitude;

    public $latitude;

    function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        $this->initFrom($args, [
            'longitude' => 'longitude',
            'latitude' => 'latitude',
        ]);
    }
}
