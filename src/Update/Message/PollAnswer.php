<?php

namespace Mmb\Update\Message; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\User\User;

class PollAnswer extends MmbBase implements \Mmb\Update\Interfaces\IUserID 
{
    
    /**
     * شی اصلی این کلاس
     * 
     * @var static
     */
    public static $this;


    /**
     * @var Mmb
     */
    private $_base;

    /**
     * شناسه نظرسنجی
     *
     * @var string
     */
    public $id;
    /**
     * کاربر رای دهنده
     *
     * @var User
     */
    public $user;
    /**
     * گزینه های انتخاب شده
     *
     * @var int[]
     */
    public $options;
    /**
     * تعداد انتخاب ها
     *
     * @var int
     */
    public $chosenCount;

    public function __construct($a, Mmb $base)
    {

        if($base->loading_update && !static::$this)
            self::$this = $this;

        $this->_base = $base;
        $this->id = $a['poll_id'];
        $this->user = new User($a['user'], $base);
        $this->options = $a['option_ids'];
        $this->chosenCount = count($this->options);
        
    }

    
	/**
	 * گرفتن آیدی کاربر
	 *
	 * @return int
	 */
	function IUserID() {
        
        return $this->user->IUserID();

	}
}
