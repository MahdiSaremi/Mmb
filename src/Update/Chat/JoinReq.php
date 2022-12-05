<?php

namespace Mmb\Update\Chat; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\User\User;

class JoinReq extends MmbBase implements \Mmb\Update\Interfaces\IChatID, \Mmb\Update\Interfaces\IUserID
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
     * چت
     *
     * @var Chat
     */
    public $chat;
    /**
     * کاربر
     *
     * @var User
     */
    public $from;
    /**
     * تاریخ
     *
     * @var int
     */
    public $date;
    /**
     * بیوگرافی کاربر
     *
     * @var string
     */
    public $bio;
    /**
     * لینک دعوت
     *
     * @var Invite
     */
    public $inviteLink;

    public function __construct($a, $base)
    {

        if($base->loading_update && !static::$this)
            self::$this = $this;

        $this->_base = $base;
        $this->chat = new Chat($a['chat'], $base);
        $this->from = new User($a['from'], $base);
        $this->date = $a['date'];
        $this->bio = @$a['bio'];
        if($_ = $a['invite_link'])
            $this->inviteLink = new Invite($_, $this->chat->id, $base);
            
    }

    /**
     * تایید درخواست عضویت
     *
     * @return bool
     */
    public function approve(){
        return $this->_base->approveJoinReq($this->chat->id, $this->from->id);
    }

    /**
     * رد کردن درخواست عضویت
     *
     * @return bool
     */
    public function decline(){
        return $this->_base->declineJoinReq($this->chat->id, $this->from->id);
    }
    
    
	/**
	 * گرفتن آیدی چت
	 *
	 * @return int
	 */
	function IChatID() {
        
        return $this->chat->id;

	}
	
	/**
	 * گرفتن آیدی کاربر
	 *
	 * @return int
	 */
	function IUserID() {
        
        return $this->from->id;

	}
}
