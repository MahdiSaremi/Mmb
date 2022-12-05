<?php

// Copyright (C): t.me/MMBlib

namespace Mmb\Update\Chat; #auto

use Mmb\MmbBase;
use Mmb\Update\User\User;

class MemberUpd extends MmbBase implements \Mmb\Update\Interfaces\IChatID, \Mmb\Update\Interfaces\IUserID
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
     * وضعیت قدیمی کاربر
     *
     * @var Member
     */
    public $old;
    /**
     * وضعیت جدید کاربر
     *
     * @var Member
     */
    public $new;
    /**
     * لینکی که کاربر با آن دعونت شده
     *
     * @var Invite
     */
    public $inviteLink;

    /**
     * آیا چت خصوصی است
     *
     * @var bool
     */
    public $isPrivate;
    /**
     * آیا کاربر ربات را شروع کرد
     *
     * @var bool
     */
    public $isStart;
    /**
     * آیا کاربر ربات را بلاک کرد
     *
     * @var bool
     */
    public $isStop;

    public function __construct($a, $base)
    {

        if($base->loading_update && !static::$this)
            self::$this = $this;

        $this->_base = $base;
        $this->chat = new Chat($a['chat'], $base);
        $this->from = new User($a['from'], $base);
        $this->date = $a['date'];
        $this->old = new Member($a['old_chat_member'], $base);
        $this->new = new Member($a['new_chat_member'], $base);
        if($_ = $a['invite_link'])
            $this->inviteLink = new Invite($_, $this->chat->id, $base);

        $this->isPrivate = $this->chat->type == Chat::TYPE_PRIVATE;
        if($this->isPrivate){
            $this->isStart = $this->old->status == Member::STATUS_KICKED &&
                                $this->new->status == Member::STATUS_MEMBER;
            $this->isStop = $this->old->status == Member::STATUS_MEMBER &&
                                $this->new->status == Member::STATUS_KICKED;
        }
        else{
            $this->isStart = false;
            $this->isStop = false;
        }

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
