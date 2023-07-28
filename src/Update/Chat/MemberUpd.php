<?php

// Copyright (C): t.me/MMBlib

namespace Mmb\Update\Chat; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\User\UserInfo;

class MemberUpd extends MmbBase implements \Mmb\Update\Interfaces\IChatID, \Mmb\Update\Interfaces\IUserID
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
    /**
     * آیا کاربر در کانال/گروه عضو شده است
     *
     * @var boolean
     */
    public $isJoined;
    /**
     * آیا کاربر از کانال/گروه خارج شده است
     *
     * @var boolean
     */
    public $isLeft;

    public function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        if($this->_base->loading_update && !static::$this)
            self::$this = $this;

        $this->initFrom($args, [
            'chat' => fn($chat) => $this->chat = new Chat($chat, $this->_base),
            'from' => fn($from) => $this->from = new UserInfo($from, $this->_base),
            'date' => 'date',
            'old_chat_member' => fn($chatMember) => $this->old = new Member($chatMember, $this->_base),
            'new_chat_member' => fn($chatMember) => $this->new = new Member($chatMember, $this->_base),
            'invite_link' => fn($inv) => new Invite($inv, $this->_base),
        ]);

        if($this->old && $this->new)
        {
            $this->isJoined = ($this->old->status == Member::STATUS_KICKED || $this->old->status == Member::STATUS_LEFT) &&
                            $this->new->status == Member::STATUS_MEMBER;
            $this->isLeft = $this->old->status == Member::STATUS_MEMBER &&
                            ($this->new->status == Member::STATUS_KICKED || $this->new->status == Member::STATUS_LEFT);
        }

        $this->isPrivate = $this->chat?->type == Chat::TYPE_PRIVATE;
        if($this->isPrivate)
        {
            $this->isStart = $this->isJoined;
            $this->isStop = $this->isLeft;
        }
        else
        {
            $this->isStart = false;
            $this->isStop = false;
        }

    }


	/**
	 * گرفتن آیدی چت
	 *
	 * @return int
	 */
	function IChatID()
    {
        return $this->chat->id;
	}
	
	/**
	 * گرفتن آیدی کاربر
	 *
	 * @return int
	 */
	function IUserID()
    {
        return $this->from->id;
	}
}
