<?php

namespace Mds\Mmb\Update\Inline; #auto

use Mds\Mmb\Mmb;
use Mds\Mmb\MmbBase;
use Mds\Mmb\Update\Message\Msg;
use Mds\Mmb\Update\User\User;

class ChosenInline extends MmbBase implements \Mds\Mmb\Update\Interfaces\IUserID, \Mds\Mmb\Update\Interfaces\IMsgID 
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
     * آیدی ایتم انتخاب شده
     *
     * @var string
     */
    public $id;
    /**
     * کاربر
     *
     * @var User
     */
    public $from;
    /**
     * شناسه پیام
     *
     * @var string
     */
    public $msgID;
    /**
     * پیام فیک
     *
     * @var Msg
     */
    public $msg;
    /**
     * پیام درخواست اینلاین
     *
     * @var string
     */
    public $query;
    public function __construct($r, Mmb $base)
    {

        if($base->loading_update && !static::$this)
            self::$this = $this;

        $this->_base = $base;
        $this->id = $r['result_id'];
        $this->from = new User($r['from'], $base);
        $this->msgID = @$r['inline_message_id'];
        if($this->msgID)
            $this->msg = new Msg($this->msgID, $base, true);
        $this->query = $r['query'];

    }

	/**
	 * گرفتن آیدی کاربر
	 *
	 * @return int
	 */
	function IUserID() {
        
        return $this->from->IUserID();

	}
	
	/**
	 * گرفتن آیدی پیام
	 *
	 * @return int
	 */
	function IMsgID() {
        
        return $this->msg->IUserID();

	}
}
