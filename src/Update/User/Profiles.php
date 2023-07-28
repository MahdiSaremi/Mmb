<?php

namespace Mmb\Update\User; #auto

use ArrayAccess;
use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\Message\Data\Media;

class Profiles extends MmbBase implements ArrayAccess
{
    /**
     * عکس ها
     *
     * @var Media[][]
     */
    public array $photos;

    /**
     * تعداد کل
     *
     * @var int
     */
    public $count;

    public function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        $this->count = $args['total_count'] ?? 0;
        $this->photos = [];
        foreach($args['photos'] ?? [] as $once)
        {
            $pics = [];
            foreach($once as $pic)
            {
                $pics[] = new Media("photo", $pic, $this->_base);
            }
            $this->photos[] = $pics;
        }
    }
	
    /**
     * @return bool
     */
	public function offsetExists($offset) : bool
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
	
	public function offsetSet($offset, $value) : void
    {
        $this->photos[$offset] = $value;
	}
	
	public function offsetUnset($offset) : void
    {
        unset($this->photos[$offset]);
	}
}
