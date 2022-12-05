<?php

// Copyright (C): t.me/MMBlib

namespace Mds\Mmb\Update\Chat; #auto

use Mds\Mmb\Mmb;
use Mds\Mmb\MmbBase;
use Mds\Mmb\Update\Message\Msg;

class Chat extends MmbBase implements \Mds\Mmb\Update\Interfaces\IChatID
{
    
    /**
     * شی اصلی این کلاس
     * 
     * @var static
     */
    public static $this;

    
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
     * @var Photo|null
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
    /**
     * @var Mmb
     */
    private $_base;
   public function __construct($c, Mmb $base){

        if($base->loading_update && !static::$this)
            self::$this = $this;

        $this->_base = $base;
        $this->id = $c['id'];
        $this->type = @$c['type'];
        $this->title = @$c['title'];
        $this->username = @$c['username'];
        $this->firstName = @$c['first_name'];
        $this->lastName = @$c['last_name'];
        $this->name = $this->firstName . ($this->lastName ? ' ' . $this->lastName : '');
        $this->bio = @$c['bio'];
        if(@$c['des'])
            $this->bio = @$c['des'];
        if($_ = @$c['photo'])
            $this->photo = new Photo($_, $base);
        $this->inviteLink = @$c['invite_link'];
        $this->pinnedMsg = @$c['pinned_message'];
        $this->slowDelay = @$c['slow_mode_delay'];
        $this->linkedChatID = @$c['linked_chat_id'];
        if(isset($c['permissions'])){
            $this->pers = new Per($c['permissions'], null, $base);
        }

    }
    
    /**
     * گرفتن اطلاعات کاربر در چت
     *
     * @param mixed $user
     * @return Member|false
     */
   public function getMember($user){
        if(is_array($user)){
            $user['chat'] = $this->id;
            return $this->_base->getChatMember($user);
        }
        return $this->_base->getChatMember($this->id, $user);
    }
    
    /**
     * گرفتن تعداد عضو های چت
     *
     * @return int|false
     */
   public function getMemberNum(){
        return $this->_base->getChatMemberNum($this->id);
    }
    
    /**
     * گرفتن تعداد عضو های چت
     *
     * @return int|false
     */
   public function getMemberCount(){
        return $this->_base->getChatMemberCount($this->id);
    }
    
    /**
     * حذف کاربر از چت
     *
     * @param mixed $user
     * @param int $until
     * @return bool|false
     */
   public function ban($user, $until = null) {
        return $this->_base->ban($this->id, $user, $until);
    }
    
    /**
     * رفع مسدودیت کاربر از چت
     *
     * @param mixed $user
     * @return bool|false
     */
   public function unban($user){
        return $this->_base->unban($this->id, $user);
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
        if(is_array($user)){
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
        if(is_array($user)){
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
        if($per instanceof \JsonSerializable) $per = $per->jsonSerialize();
        if(isset($per['per'])){
            $per['chat'] = $this->id;
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
   public function createInviteLink(array $args){
        $args['chat'] = $this->id;
        return $this->createInviteLink($args);
    }

    /**
     * ویرایش لینک دعوت
     * [chat-link-name-expire-limit-joinReq]
     *
     * @param array $args
     * @return Invite|false
     */
    public function editInviteLink($args){
        $args['chat'] = $this->id;
        return $this->editInviteLink($args);
    }
    
    /**
     * تنظیم عکس چت
     *
     * @param mixed $photo
     * @return bool
     */
   public function setPhoto($photo){
        return $this->_base->setChatPhoto($this->id, $photo);
    }
    
    /**
     * حذف عکس چت
     *
     * @return bool
     */
   public function delPhoto(array $args){
        $args['chat'] = $this->id;
        return $this->_base->delChatPhoto($args);
    }
    
    /**
     * تنظیم عنوان چت
     *
     * @param string $title
     * @return bool
     */
   public function setTitle($title){
        return $this->_base->setChatTitle($this->id, $title);
    }
    
    /**
     * تنظیم توضیحات گروه
     *
     * @param string $des Description | توضیحات
     * @return bool
     */
   public function setDes($des){
        return $this->_base->setChatDes($this->id, $des);
    }
    
    /**
     * سنجاق کردن پیام
     *
     * @param mixed $msg Message id or message object | آیدی یا شئ پیام
     * @return bool
     */
   public function pin($msg){
        return $this->_base->pinMsg($this->id, $msg);
    }
    
    /**
     * حذف سنجاق پیام
     *
     * @param mixed $msg
     * @return bool
     */
   public function unpin($msg = null){
        return $this->_base->unpinMsg($this->id, $msg);
    }
    
    /**
     * حذف سنجاق تمامی پیام های سنجاق شده
     *
     * @param mixed $msg
     * @return bool
     */
   public function unpinAll(array $args = []){
        $args['chat'] = $this->id;
        return $this->_base->unpinAll($args);
    }
    
    /**
     * ترک چت
     *
     * @return bool
     */
   public function leave(array $args = []){
        $args['chat'] = $this->id;
        return $this->_base->leave($args);
    }
    
    /**
     * گرفتن لیست ادمین ها
     *
     * @return Member[]|false
     */
   public function getAdmins(array $args = []){
        $args['chat'] = $this->id;
        return $this->_base->getChatAdmins($args);
    }
    
    /**
     * تنظیم بسته استیکر
     *
     * @param string $setName
     * @return bool
     */
   public function setStickerSet($setName){
        return $this->_base->setChatStickerSet($this->id, $setName);
    }
    
    /**
     * حذف بسته استیکر
     *
     * @return bool
     */
   public function delStickerSet(){
        return $this->_base->delChatStickerSet($this->id);
    }

    /**
     * ارسال حالت چت
     *
     * @param mixed $action
     * @return bool
     */
   public function action($action){
        return $this->_base->action($this->id, $action);
    }

    /**
     * ارسال پیام به چت
     *
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
   public function sendMsg($text, $args = []) {

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
   public function send($type, $args = []){
        $args['chat'] = $this->id;
        return $this->_base->send($type, $args);
    }

	/**
	 * گرفتن آیدی چت
	 *
	 * @return int
	 */
	public function IChatID() {

        return $this->id;
        
	}
}
