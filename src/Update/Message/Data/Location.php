<?php

namespace Mds\Mmb\Update\Message\Data; #auto

use Mds\Mmb\Mmb;
use Mds\Mmb\MmbBase;

class Location extends MmbBase 
{

    public $longitude;

    public $latitude;

    /**
     * @var Mmb
     */
    private $_base;
    function __construct($loc, $base){
        $this->_base = $base;
        $this->longitude = $loc['longitude'];
        $this->latitude = $loc['latitude'];
    }
}
