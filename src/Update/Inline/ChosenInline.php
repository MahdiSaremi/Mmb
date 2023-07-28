<?php

namespace Mmb\Update\Inline; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\Message\Msg;
use Mmb\Update\User\UserInfo;

class ChosenInline extends MmbBase implements \Mmb\Update\Interfaces\IUserID, \Mmb\Update\Interfaces\IMsgID 
{
    
    /**
     * شی اصلی این کلاس
     * 
     * @var static
     */
    public static $this;
    public static function this()
    {
        return static::$this;
    }


    /**
     * آیدی ایتم انتخاب شده
     *
     * @var string
     */
    public $id;
    /**
     * کاربر
     *
     * @var UserInfo
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
    public function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        if($this->_base->loading_update && !static::$this)
            self::$this = $this;

        $this->initFrom($args, [
            'result_id' => 'id',
            'from' => fn($from) => $this->from = new UserInfo($from, $this->_base),
            'inline_message_id' => 'msgID',
            'query' => 'query',
        ]);
        
        if($this->msgID)
        {
            $this->msg = new Msg([], $this->_base, true, $this->msgID);
        }
    }

	/**
	 * گرفتن آیدی کاربر
	 *
	 * @return int
	 */
	function IUserID()
    {
        return $this->from->IUserID();
	}
	
	/**
	 * گرفتن آیدی پیام
	 *
	 * @return int
	 */
	function IMsgID()
    {
        return $this->msg->IUserID();
	}
}
