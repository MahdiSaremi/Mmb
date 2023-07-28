<?php

namespace Mmb\Update\Chat; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\User\UserInfo;

class JoinReq extends MmbBase implements \Mmb\Update\Interfaces\IChatID, \Mmb\Update\Interfaces\IUserID
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
     * چت
     *
     * @var Chat
     */
    public $chat;
    /**
     * کاربر
     *
     * @var UserInfo
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

    public function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        if($this->_base->loading_update && !static::$this)
            self::$this = $this;

        $this->initFrom($args, [
            'chat' => fn($chat) => new Chat($chat, $this->_base),
            'from' => fn($from) => new UserInfo($from, $this->_base),
            'date' => 'date',
            'bio' => 'bio',
            'invite_link' => fn($inv) => $this->inviteLink = new Invite($inv, $this->chat?->id, $this->_base),
        ]);
    }

    /**
     * تایید درخواست عضویت
     *
     * @return bool
     */
    public function approve()
    {
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
