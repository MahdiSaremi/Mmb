<?php

// Copyright (C): t.me/MMBlib

namespace Mmb\Update\Chat; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\Message\Msg;

class Chat extends MmbBase implements \Mmb\Update\Interfaces\IChatID
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
     * آیدی چت
     *
     * @var int
     */
    public $id;
    /**
     * نوع چت
     *
     * @var string
     */
    public $type;
    public const TYPE_PRIVATE = 'private';
    public const TYPE_GROUP = 'group';
    public const TYPE_SUPERGROUP = 'supergroup';
    public const TYPE_CHANNEL = 'channel';

    /**
     * عنوان چت
     *
     * @var string|null
     */
    public $title;

    /**
     * نام کاربری چت
     *
     * @var string|null
     */
    public $username;
    /**
     * نام کوچک
     *
     * @var string|null
     */
    public $firstName;
    /**
     * نام بزرگ
     *
     * @var string|null
     */
    public $lastName;
    /**
     * نام کامل
     *
     * @var string
     */
    public $name;
    /**
     * بیوگرافی کاربر یا گروه یا کانال
     *
     * @var string|null
     */
    public $bio;
    /**
     * عکس پروفایل
     *
     * @var ChatPhoto|null
     */
    public $photo;
    /**
     * لینک دعوت
     *
     * @var string|null
     */
    public $inviteLink;
    /**
     * پیغام سنجاق شده در چت
     *
     * @var Msg|null
     */
    public $pinnedMsg;
    /**
     * تاخیر حالت آهسته
     *
     * @var int|null
     */
    public $slowDelay;
    /**
     * آیدی گروه یا کانال متصل به چت
     *
     * @var int|null
     */
    public $linkedChatID;
    /**
     * دسترسی های گروه
     * 
     * تنها در تابع گت چت مقدار داده می شود
     *
     * @var Per|null
     */
    public $pers;
    public function __construct(array $args, ?Mmb $mmb = null)
	{
        parent::__construct($args, $mmb);

        if($this->_base->loading_update && !static::$this)
            self::$this = $this;

        $this->initFrom($args, [
            'id' => 'id',
            'type' => 'type',
            'title' => 'title',
            'username' => 'username',
            'first_name' => 'firstName',
            'last_name' => 'lastName',
            'bio' => 'bio',
            'des' => 'des',
            'photo' => fn($photo) => $this->photo = new ChatPhoto($photo, $this->_base),
            'invite_link' => 'inviteLink',
            'pinned_message' => 'pinnedMsg',
            'slow_mode_delay' => 'slowDelay',
            'linked_chat_id' => 'linkedChatID',
            'permissions' => fn($per) => $this->pers = new Per($per, null, $this->_base),
        ]);

        $this->name = $this->firstName . ($this->lastName ? ' ' . $this->lastName : '');
    }

    /**
     * یک شی چت با این آیدی می سازد تا بتوانید از متد های آن استفاده کنید
     * 
     * این متد اطلاعات چت را از تلگرام نمی خواند
     *
     * @param string|integer $id
     * @param ?Mmb $mmb
     * @return Chat
     */
    public static function of(string|int $id, ?Mmb $mmb = null)
    {
        return new Chat([ 'id' => $id ], $mmb ?? mmb());
    }

    /**
     * اطلاعات چتی را از تلگرام می خواند و بر می گرداند
     *
     * @param string|integer $id
     * @param Mmb|null $mmb
     * @return Chat|false
     */
    public static function get(string|int $id, ?Mmb $mmb = null)
    {
        return ($mmb ?? mmb())->getChat($id);
    }
    
    /**
     * گرفتن اطلاعات کاربر در چت
     *
     * @param mixed|array $user
     * @return Member|false
     */
	public function getMember($user)
	{
		$args = maybeArray([
			'chat' => $this->id,
			'user' => $user,
		]);
        return $this->_base->getChatMember($args);
    }
    
    /**
     * گرفتن تعداد عضو های چت
     *
     * @return int|false
     */
   	public function getMemberNum(array $args = [])
	{
		$args = maybeArray([
			'chat' => $this->id,
			'args' => $args,
		]);
        return $this->_base->getChatMemberNum($this->id);
    }
    
    /**
     * گرفتن تعداد عضو های چت
     *
     * @return int|false
     */
   	public function getMemberCount(array $args = [])
	{
		$args = maybeArray([
			'chat' => $this->id,
			'args' => $args,
		]);
        return $this->_base->getChatMemberCount($this->id);
    }
    
    /**
     * حذف کاربر از چت
     *
     * @param mixed $user
     * @param int $until
     * @return bool|false
     */
   	public function ban($user, $until = null)
	{
		$args = maybeArray([
			'chat' => $this->id,
			'user' => $user,
			'until' => $until,
		]);
        return $this->_base->ban($args);
    }
    
    /**
     * رفع مسدودیت کاربر از چت
     *
     * @param mixed $user
     * @return bool|false
     */
   	public function unban($user)
	{
		$args = maybeArray([
			'chat' => $this->id,
			'user' => $user,
		]);
        return $this->_base->unban($args);
    }
    
    /**
     * محدود کردن کاربر
     *
     * @param mixed $user
     * @param array $per
     * @param int $until
     * @return bool
     */
   	public function restrict($user, $per = [], $until = null)
   	{
        if(is_array($user))
		{
			if(!isset($user['chat']))
				$user['chat'] = $this->id;
            return $this->_base->restrict($user);
        }
        return $this->_base->restrict($this->id, $user, $per, $until);
    }
    
    /**
     * ترفیع کاربر
     *
     * @param mixed $user
     * @param array $per
     * @return bool
     */
	public function promote($user, $per = [])
	{
        if(is_array($user))
		{
			if(!isset($user['chat']))
				$user['chat'] = $this->id;
            return $this->_base->promote($user);
        }
        return $this->_base->promote($this->id, $user, $per);
    }
    
    /**
     * تنظیم دسترسی های گروه
     *
     * @param array $per
     * @return bool
     */
	public function setPer($per)
	{
        if($per instanceof \JsonSerializable)
			$per = $per->jsonSerialize();
        if(isset($per['per']))
		{
			if(!isset($user['chat']))
				$user['chat'] = $this->id;
            return $this->_base->setChatPer($per);
        }
        return $this->_base->setChatPer($this->id, $per);
    }
    
    /**
     * گرفتن لینک دعوت چت
     *
     * @return string
     */
	public function getInviteLink(array $args = [])
	{
		if(!isset($args['chat']))
			$args['chat'] = $this->id;
        return $this->_base->getInviteLink($args);
    }
    
    /**
     * ساخت لینک دعوت
     * [chat-name-expire-limit-joinReq]
     *
     * @param array $args
     * @return Invite|false
     */
   	public function createInviteLink(array $args)
	{
		if(!isset($args['chat']))
			$args['chat'] = $this->id;
        return $this->_base->createInviteLink($args);
    }

    /**
     * ویرایش لینک دعوت
     * [chat-link-name-expire-limit-joinReq]
     *
     * @param array $args
     * @return Invite|false
     */
    public function editInviteLink($args)
	{
		if(!isset($args['chat']))
			$args['chat'] = $this->id;
        return $this->_base->editInviteLink($args);
    }
    
    /**
     * تنظیم عکس چت
     *
     * @param mixed $photo
     * @return bool
     */
   	public function setPhoto($photo)
	{
		$args = maybeArray([
			'chat' => $this->id,
			'photo' => $photo,
		]);
        return $this->_base->setChatPhoto($args);
    }
    
    /**
     * حذف عکس چت
     *
     * @return bool
     */
   	public function delPhoto(array $args)
	{
		if(!isset($args['chat']))
			$args['chat'] = $this->id;
        return $this->_base->delChatPhoto($args);
    }
    
    /**
     * تنظیم عنوان چت
     *
     * @param string $title
     * @return bool
     */
   	public function setTitle($title)
	{
		$args = maybeArray([
			'chat' => $this->id,
			'title' => $title,
		]);
        return $this->_base->setChatTitle($args);
    }
    
    /**
     * تنظیم توضیحات گروه
     *
     * @param string $des Description | توضیحات
     * @return bool
     */
   	public function setDes($des)
	{
		$args = maybeArray([
			'chat' => $this->id,
			'des' => $des,
		]);
        return $this->_base->setChatDes($args);
    }
    
    /**
     * سنجاق کردن پیام
     *
     * @param mixed $msg Message id or message object | آیدی یا شئ پیام
     * @return bool
     */
   	public function pin($msg)
	{
		$args = maybeArray([
			'chat' => $this->id,
			'msg' => $msg,
		]);
        return $this->_base->pinMsg($args);
    }
    
    /**
     * حذف سنجاق پیام
     *
     * @param mixed $msg
     * @return bool
     */
   	public function unpin($msg = null)
	{
		$args = maybeArray([
			'chat' => $this->id,
			'msg' => $msg,
		]);
        return $this->_base->unpinMsg($args);
    }
    
    /**
     * حذف سنجاق تمامی پیام های سنجاق شده
     *
     * @param mixed $msg
     * @return bool
     */
   	public function unpinAll(array $args = [])
	{
		if(!isset($args['chat']))
			$args['chat'] = $this->id;
        return $this->_base->unpinAll($args);
    }
    
    /**
     * ترک چت
     *
     * @return bool
     */
   	public function leave(array $args = [])
	{
		if(!isset($args['chat']))
			$args['chat'] = $this->id;
        return $this->_base->leave($args);
    }
    
    /**
     * گرفتن لیست ادمین ها
     *
     * @return Member[]|false
     */
   	public function getAdmins(array $args = [])
	{
		if(!isset($args['chat']))
			$args['chat'] = $this->id;
        return $this->_base->getChatAdmins($args);
    }
    
    /**
     * تنظیم بسته استیکر
     *
     * @param string|array $setName
     * @return bool
     */
   	public function setStickerSet($setName)
	{
		$args = maybeArray([
			'chat' => $this->id,
			'setName' => $setName,
		]);
        return $this->_base->setChatStickerSet($args);
    }
    
    /**
     * حذف بسته استیکر
     *
     * @return bool
     */
   	public function delStickerSet($args = [])
	{
		if(!isset($args['chat']))
			$args['chat'] = $this->id;
        return $this->_base->delChatStickerSet($args);
    }

    /**
     * ارسال حالت چت
     *
     * @param mixed $action
     * @return bool
     */
   	public function action($action)
	{
		$args = maybeArray([
			'chat' => $this->id,
			'action' => $action,
		]);
        return $this->_base->action($args);
    }

    /**
     * ارسال پیام به چت
     *
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
   	public function sendMsg($text, array $args = [])
	{
        $args = maybeArray([
            'chat' => $this->id,
            'text' => $text,
            'args' => $args
        ]);
        return $this->_base->sendMsg($args);
    }
    
    /**
     * ارسال پیام به چت با ارسال پیامی با نوع دلخواه
     *
     * @param string|array $type
     * @param array $args
     * @return Msg|false
     */
   	public function send($type, array $args = [])
	{
		$args = maybeArray([
			'chat' => $this->id,
			'type' => $type,
			'args' => $args,
		]);
        return $this->_base->send($args);
    }

	/**
	 * گرفتن آیدی چت
	 *
	 * @return int
	 */
	public function IChatID()
	{
        return $this->id;
	}
}
