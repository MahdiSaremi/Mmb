<?php

namespace Mds\Mmb\Update\User; #auto

use ArrayAccess;
use Mds\Mmb\Mmb;
use Mds\Mmb\MmbBase;
use Mds\Mmb\Update\Message\Data\Media;

class Profiles extends MmbBase implements ArrayAccess
{
    /**
     * عکس ها
     *
     * @var Media[][]
     */
    public $photos;

    /**
     * تعداد کل
     *
     * @var int
     */
    public $count;

    /**
     * @var Mmb
     */
    private $_base;
    public function __construct($v, $base){
        $this->_base = $base;
        $this->count = $v['total_count'];
        $this->photos = [];
        foreach($v['photos']as$once){
            $a=[];
            foreach($once as $x)
                $a[] = new Media("photo", $x, $base);
            $this->photos[] = $a;
        }
    }
	
    /**
     * @return bool
     */
	public function offsetExists($offset)
    {
        return isset($this->photos[$offset]);
	}
	
	
    /**
     * @return Media[]
     */
	public function offsetGet($offset) 
    {
        return $this->photos[$offset];
	}
	
	public function offsetSet($offset, $value) 
    {
        $this->photos[$offset] = $value;
	}
	
	public function offsetUnset($offset) 
    {
        unset($this->photos[$offset]);
	}
}
