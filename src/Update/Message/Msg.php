<?php

// Copyright (C): t.me/MMBlib

namespace Mmb\Update\Message; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\Chat\Chat;
use Mmb\Update\Message\Data\Contact;
use Mmb\Update\Message\Data\Dice;
use Mmb\Update\Message\Data\Entity;
use Mmb\Update\Message\Data\Location;
use Mmb\Update\Message\Data\Media;
use Mmb\Update\Message\Data\Poll;
use Mmb\Update\Message\Data\Sticker;
use Mmb\Update\User\InChat;
use Mmb\Update\User\User;

class Msg extends MmbBase implements \Mmb\Update\Interfaces\IMsgID, \Mmb\Update\Interfaces\IUserID, \Mmb\Update\Interfaces\IChatID
{
    
    /**
     * شی اصلی این کلاس
     * 
     * @var static
     */
    public static $this;


    /**
     * مقدار های قابل قبول کد استارت(به صورت کد ریجکس)
     * 
     * @var string $acceptStartCode
     */
    public static $acceptStartCode = '^\s\n\r';
    /**
     * @var Mmb
     */
    private $_base;
    /**
     * آیدی عددی پیام
     *
     * @var int|null
     */
    public $id;
    /**
     * آیا پیام مربوط به حالت اینلاین است
     *
     * @var bool
     */
    public $isInline;
    /**
     * شناسه پیام برای حالت اینلااین
     *
     * @var string
     */
    public $inlineID;
    /**
     * آیا ربات استارت شده؟
     *
     * @var bool
     */
    public $started;
    /**
     * کد استارت
     * 
     * /start [CODE]
     *
     * @var string|null
     */
    public $startCode = null;
    /**
     * اگر پیام کاربر با / شروع شود، متن دستور را به شما می دهد
     *
     * @var string|null
     */
    public $command = null;
    private $commandLower = null;
    /**
     * اگر پیام کاربر با / شروع شود، متن مقابل دستور را به شما می دهد
     *
     * @var string|null
     */
    public $commandData = null;
    /**
     * اگر پیام کاربر با / شروع شود، آیدی همراه با @ جلوی دستور را میدهد
     *
     * @var string|null
     */
    public $commandTag = null;
    /**
     * متن یا عنوان پیام
     *
     * @var string|null
     */
    public $text;
    /**
     * نوع پیام
     *
     * @var string|null
     */
    public $type;
    /**
     * رسانه های ام.ام.بی (عکس، مستند، ویس، فیلم، گیف، صدا، استیکر)
     *
     * @var Media|null
     */
    public $media;
    /**
     * آیدی رسانه های ام.ام.بی
     *
     * @var string
     */
    public $media_id;
    /**
     * تصویر
     *
     * @var Media[]|null
     */
    public $photo;
    /**
     * مستند
     *
     * @var Media|null
     */
    public $doc;
    /**
     * صدا
     *
     * @var Media|null
     */
    public $voice;
    /**
     * فیلم
     *
     * @var Media|null
     */
    public $video;
    /**
     * گیف
     *
     * @var Media|null
     */
    public $anim;
    /**
     * صوت
     *
     * @var Media|null
     */
    public $audio;
    /**
     * ویدیو سلفی
     *
     * @var Media|null
     */
    public $videoNote;
    /**
     * مختصات
     *
     * @var Location|null
     */
    public $location;
    /**
     * شانس
     *
     * @var Dice|null
     */
    public $dice;
    /**
     * نظرسنجی
     *
     * @var Poll|null
     */
    public $poll;
    /**
     * مخاطب
     *
     * @var Contact|null
     */
    public $contact;
    /**
     * استیکر
     *
     * @var Sticker|null
     */
    public $sticker;
    /**
     * عضو های جدید
     *
     * @var User[]|null
     */
    public $newMembers;
    /**
     * عضو ترک شده
     *
     * @var User|null
     */
    public $leftMember;
    /**
     * عنوان جدید
     *
     * @var string
     */
    public $newTitle;
    /**
     * تصویر پروفایل جدید
     *
     * @var Media[]
     */
    public $newPhoto;
    /**
     * حذف تصویر پروفایل
     *
     * @var bool
     */
    public $delPhoto;
    /**
     * گروه جدید
     *
     * @var bool
     */
    public $newGroup;
    /**
     * سوپر گروه جدید
     *
     * @var bool
     */
    public $newSupergroup;
    /**
     * کانال جدید
     *
     * @var bool
     */
    public $newChannel;
    /**
     * پیام ریپلای شده
     *
     * @var Msg|null
     */
    public $reply;
    /**
     * اطلاعات چت
     *
     * @var Chat|null
     */
    public $chat;
    /**
     * چت ارسال کننده
     *
     * @var Chat|null
     */
    public $sender;
    /**
     * اطلاعات ارسال کننده
     *
     * @var User|null
     */
    public $from;
    /**
     * تاریخ ارسال پیام
     *
     * @var int|null
     */
    public $date;
    /**
     * آیدی آلبوم
     *
     * @var string
     */
    public $mediaGroupID;
    /**
     * ویرایش شده؟
     *
     * @var bool
     */
    public $edited;
    /**
     * تاریخ ویرایش پیام
     *
     * @var int|null
     */
    public $editDate;
    /**
     * باز ارسال شده؟
     *
     * @var bool
     */
    public $forwarded;
    /**
     * کاربری که پیام آن باز ارسال شده است (در صورت باز ارسال از کاربر)
     *
     * @var User|null
     */
    public $forwardFrom;
    /**
     * چتی که پیام از آنجا باز ارسال شده است (در صورت باز ارسال از چت)
     *
     * @var Chat|null
     */
    public $forwardChat;
    /**
     * آیدی پیام در چت باز ارسال شده (در صورت باز ارسال از چت)
     *
     * @var int|null
     */
    public $forwardMsgId;
    /**
     * امضای پیام (در صورت باز ارسال از چت و داشتن امضا)
     *
     * @var string|null
     */
    public $forwardSig;
    /**
     * تاریخ پیام باز ارسال شده
     *
     * @var int|null
     */
    public $forwardDate;
    /**
     * نهاد ها(علائمی همچون لینک، تگ، منشن و ...)
     *
     * @var Entity[]|null
     */
    public $entities;
    /**
     * پیام سنجاق شده
     *
     * @var Msg|null
     */
    public $pinnedMsg;
    /**
     * دکمه های پیام
     *
     * @var array|null
     */
    public $key;
    /**
     * رباتی که پیغام توسط آن ایجاد شده
     *
     * @var User|null
     */
    public $via;
    /**
     * کاربر در چت
     *
     * @var InChat|null
     */
    public $userInChat;

    public const TYPE_TEXT = 'text';
    public const TYPE_PHOTO = 'photo';
    public const TYPE_VOICE = 'voice';
    public const TYPE_VIDEO = 'video';
    public const TYPE_ANIM = 'anim';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_VIDEO_NOTE = 'video_note';
    public const TYPE_LOCATION = 'location';
    public const TYPE_DICE = 'dice';
    public const TYPE_STICKER = 'sticker';
    public const TYPE_CONTACT = 'contact';
    public const TYPE_DOC = 'doc';
    public const TYPE_POLL = 'poll';
    
    public const TYPE_NEW_MEMBERS = 'new_members';
    public const TYPE_LEFT_MEMBER = 'left_member';
    public const TYPE_NEW_TITLE = 'new_title';
    public const TYPE_NEW_PHOTO = 'new_photo';
    public const TYPE_DEL_PHOTO = 'del_photo';
    public const TYPE_NEW_GROUP = 'new_group';
    public const TYPE_NEW_SUPERGROUP = 'new_supergroup';
    public const TYPE_NEW_CHANNEL = 'new_channel';

    function __construct($msg, Mmb $base, $isInline = false){

        if($base->loading_update && !static::$this)
            self::$this = $this;

        $this->_base = $base;

        if($isInline){
            $this->isInline = true;
            $this->inlineID = $msg;
            return;
        }
        $this->isInline = false;

        $this->id = $msg['message_id'];
        $this->started = false;

        if(isset($msg['chat'])){
            $this->chat = new Chat($msg['chat'], $base);
        }
        if(isset($msg['from'])){
            $this->from = new User($msg['from'], $base);
        }
        
        // Text
        if(isset($msg['text'])){
            $this->text = $msg['text'];
            $this->type = "text";

            // Command
            if(@$this->text[0] == "/") {

                // Start command
                if($this->started = preg_match('/^\/start(\s(['.(self::$acceptStartCode).']+)|)$/i', $this->text, $r))
                    $this->startCode = @$r[2];

                // All commands
                if(preg_match('/^\/([a-zA-Z0-9_]+)(@[a-zA-Z0-9_]+|)/', $this->text, $r)){
                    $this->command = $r[1];
                    $this->commandTag = $r[2];
                    $this->commandData = ltrim(substr($this->text, strlen($r[0])));
                }

            }
        }

        // Caption
        elseif(isset($msg['caption'])) {

            $this->text = $msg['caption'];

        }


        if(isset($msg['photo'])){
            $this->type = "photo";
            $this->photo = [];
            foreach($msg['photo'] as $a){
                $this->photo[] = new Media("photo", $a, $base);
            }
            $this->media = end($this->photo);
            $this->media_id = $this->media->id;
        }
        elseif(isset($msg['voice'])){
            $this->type = "voice";
            $this->media = new Media("voice", $msg['voice'], $base);
            $this->media_id = $this->media->id;
            $this->voice = $this->media;
        }
        elseif(isset($msg['video'])){
            $this->type = "video";
            $this->media = new Media("video", $msg['video'], $base);
            $this->media_id = $this->media->id;
            $this->video = $this->media;
        }
        elseif(isset($msg['animation'])){
            $this->type = "anim";
            $this->media = new Media("anim", $msg['animation'], $base);
            $this->media_id = $this->media->id;
            $this->anim = $this->media;
        }
        elseif(isset($msg['audio'])){
            $this->type = "audio";
            $this->media = new Media("audio", $msg['audio'], $base);
            $this->media_id = $this->media->id;
            $this->audio = $this->media;
        }
        elseif(isset($msg['video_note'])){
            $this->type = "video_note";
            $this->media = new Media("videonote", $msg['video_note'], $base);
            $this->media_id = $this->media->id;
            $this->videoNote = $this->media;
        }
        elseif(isset($msg['location'])){
            $this->type = "location";
            $this->location = new Location($msg['location'], $base);
        }
        elseif(isset($msg['dice'])){
            $this->type = "dice";
            $this->dice = new Dice($msg['dice'], $base);
        }
        elseif(isset($msg['poll'])){
            $this->type = "poll";
            $this->poll = new Poll($msg['poll'], $base);
        }
        elseif(isset($msg['sticker'])){
            $this->type = "sticker";
            $this->media = new Sticker($msg['sticker'], $base);
            $this->media_id = $this->media->id;
            $this->sticker = $this->media;
        }
        elseif(isset($msg['contact'])){
            $this->type = "contact";
            $this->contact = new Contact($msg['contact'], $base);
        }
        elseif(isset($msg['new_chat_members'])){
            $this->type = "new_members";
            $this->newMembers = [];
            foreach($msg['new_chat_members']as$once)
                $this->newMembers[] = new User($once, $base);
        }
        elseif(isset($msg['left_chat_member'])){
            $this->type = "left_member";
            $this->leftMember = new User($msg['left_chat_member'], $base);
        }
        elseif(isset($msg['new_chat_title'])){
            $this->type = "new_title";
            $this->newTitle = $msg['new_chat_title'];
        }
        elseif(isset($msg['new_chat_photo'])){
            $this->type = "new_photo";
            $this->newPhoto = [];
            foreach($msg['new_chat_photo'] as $once)
                $this->newPhoto[] = new Media("photo", $once, $base);
        }
        elseif(isset($msg['delete_chat_photo'])){
            $this->type = "del_photo";
            $this->delPhoto = true;
        }
        elseif(isset($msg['group_chat_created'])){
            $this->type = "new_group";
            $this->newGroup = true;
        }
        elseif(isset($msg['supergroup_chat_created'])){
            $this->type = "new_supergroup";
            $this->newSupergroup = true;
        }
        elseif(isset($msg['channel_chat_created'])){
            $this->type = "new_channel";
            $this->newChannel = true;
        }
        if(isset($msg['document'])){
            if(!$this->type){
                $this->type = "doc";
            }
            if(!$this->media){
                $this->media = new Media("doc", $msg['document'], $base);
                $this->media_id = $this->media->id;
            }
            $this->doc = $this->media;
        }
        if(isset($msg['reply_to_message'])){
            $this->reply = new Msg($msg['reply_to_message'], $base);
        }

        // Time & Date
        $this->date = @$msg['date'];
        $this->edited = isset($msg['edit_date']);
        if($this->edited)
            $this->editDate = $msg['edit_date'];

        // Forward info
        if(isset($msg['forward_from'])){
            $this->forwarded = true;
            $this->forwardFrom = new User($msg['forward_from'], $base);
        }
        elseif(isset($msg['forward_from_chat'])){
            $this->forwarded = true;
            $this->forwardChat = new Chat($msg['forward_from_chat'], $base);
            $this->forwardMsgId = $msg['forward_from_message_id'];
            $this->forwardSig = @$msg['forward_signature'];
        }
        else{
            $this->forwarded = false;
        }
        if($this->forwarded)
            $this->forwardDate = @$msg['forward_date'];

        // Entity
        if(isset($msg['entities']))
            $e = $msg['entities'];
        elseif(isset($msg['caption_entities']))
            $e = $msg['caption_entities'];
        else
            $e = [];
        $this->entities = [];
        foreach($e as $once)
            $this->entities[] = new Entity($once, $base);
            
        if(isset($msg['pinned_message'])){
            $this->pinnedMsg = new Msg($msg['pinned_message'], $base);
        }
        if(isset($msg['reply_markup'])){
            try{
                $this->key = filterArray3D($msg['reply_markup'], ['text'=>"text", 'callback_data'=>"data", 'url'=>"url", 'login_url'=>"login"],null);
            }
            catch(\Exception $e){
                $this->key = null;
            }
        }
        if($this->chat && $this->from && $this->chat->id != $this->from->id){
            $this->userInChat = new InChat($this->from, $this->chat, $base);
        }
        if($_ = @$msg['via_bot'])
            $this->via = new User($_, $base);
        if($_ = @$msg['sender_chat'])
            $this->sender = new Chat($_, $base);
        if($_ = @$msg['media_group_id'])
            $this->mediaGroupID = $_;
            
    }
    
    /**
     * پاسخ به پیام با ارسال متن
     *
     * @param string|array $text
     * @param array $args
     * @return Msg
     */
    public function replyText($text, array $args = []){

        $args = maybeArray([
            'chat' => $this->chat->id,
            'reply' => $this->id,

            'text' => $text,
            'args' => $args,
        ]);

        return $this->_base->sendMsg($args);

    }
    
    /**
     * پاسخ به پیام با ارسال پیامی با نوع دلخواه
     *
     * @param string|array $type
     * @param array $args
     * @return Msg|false
     */
    public function reply($type, array $args = []){

        $args = maybeArray([
            'chat' => $this->chat->id,
            'reply' => $this->id,

            'type' => $type,
            'args' => $args,
        ]);
        
        return $this->_base->send($args);

    }

    /**
     * ارسال پیام
     *
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
    public function sendMsg($text, $args = []){

        $args = maybeArray([
            'chat' => $this->chat->id,

            'text' => $text,
            'args' => $args,
        ]);
        
        return $this->_base->sendMsg($args);

    }
    
    /**
     * ارسال پیام با ارسال پیامی با نوع دلخواه
     *
     * @param string|array $type
     * @param array $args
     * @return Msg|false
     */
    public function send($type, $args = []){

        $args = maybeArray([
            'chat' => $this->chat->id,
            
            'type' => $type,
            'args' => $args,
        ]);
        
        return $this->_base->send($args);

    }

    /**
     * حذف پیام
     *
     * @return bool
     */
    public function del(array $args = []){

        $args['chat'] = $this->chat->id;
        $args['msg'] = $this->id;

        return $this->_base->call('deletemessage', $args);

    }
    
    /*public function edit($text, $media=null, $args=[]){
        if($this->type == "text"){
            $args = array_merge($media, $args);
            return new Msg($this->_base->call('editmessagetext', array_merge(['id'=>$this->chat->id, 'msg'=>$this->id, 'text'=>$text], $args)), $this->_base);
        }else{
            
        }
    }*/
    
    /**
     * ویرایش متن پیام
     *
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
    public function editText($text, array $args = []){

        $args = maybeArray([
            'chat' => $this->isInline ? null : $this->chat->id,
            'msg'  => $this->isInline ? null : $this->id,
            'inlineMsg' => $this->isInline ? $this->inlineID : null,

            'text' => $text,
            'args' => $args,
        ]);

        if($this->type == "text" || !$this->type) {
            return $this->_base->editMsgText($args);
        }
        else {
            return $this->_base->editMsgCaption($args);
        }
    }

    /**
     * ویرایش عنوان پیام
     *
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
    public function editCaption($text, $args = []){
        
        $args = maybeArray([
            'chat' => $this->isInline ? null : $this->chat->id,
            'msg'  => $this->isInline ? null : $this->id,
            'inlineMsg' => $this->isInline ? $this->inlineID : null,

            'text' => $text,
            'args' => $args,
        ]);

        return $this->_base->editMsgCaption($args);
    }
    
    /**
     * ویرایش دکمه های پیام
     *
     * @param array $newKey
     * @return Msg|false
     */
    public function editKey($newKey){

        if(!isset($newKey['key']))
            $newKey = [ 'key' => $newKey ];

        $args = maybeArray([
            'chat' => $this->isInline ? null : $this->chat->id,
            'msg'  => $this->isInline ? null : $this->id,
            'inlineMsg' => $this->isInline ? $this->inlineID : null,

            'args' => $newKey,
        ]);

        return objOrFalse(Msg::class, $this->_base->call('editmessagereplymarkup', $args), $this->_base);

    }
    
    /**
     * باز ارسال پیام
     *
     * @param mixed $chat Chat id
     * @return Msg|false
     */
    public function forward($chat){

        $args = maybeArray([
            'chat' => $chat,
            'msg' => $this->id,
            'from' => $this->chat->id,
        ]);

        return $this->_base->forwardMsg($args);
        
    }

    /**
     * باز ارسال پیام
     *
     * @param mixed|array $chat Chat id
     * @return Msg|false
     */
    public function forwardTo($chat){
        
        return $this->forward($chat);

    }

    /**
     * باز ارسال پیام بدون نام
     *
     * @param mixed|array $chat Chat id
     * @return Msg|false
     */
    public function copyTo($chat){

        $args = maybeArray([
            'chat' => $chat,
            'msg' => $this->id,
            'from' => $this->chat->id,
        ]);
        
        return $this->_base->copyMsg($args);

    }

    /**
     * پین کردن پیام در چت
     *
     * @param array $args
     * @return bool
     */
    public function pin(array $args = []){

        $args = maybeArray([
            'chat' => $this->chat->id,
            'msg' => $this->id,
            'args' => $args,
        ]);

        return $this->_base->pinMsg($args);

    }

    /**
     * برداشتن پین پیام از چت
     *
     * @return bool
     */
    function unpin(array $args = []) {

        $args = maybeArray([
            'chat' => $this->chat->id,
            'msg' => $this->id,
            'args' => $args,
        ]);

        return $this->_base->unpinMsg($args);

    }
    
    /**
     * ساخت ورودی از متن و محتویات پیام
     *
     * @return array|false در صورت ناموفق بودن فالس را برمیگرداند
     */
    public function createArgs(){

        if($this->type == self::TYPE_TEXT){
            return [
                'type' => 'text',
                'text' => $this->text
            ];
        }

        if($this->media){
            return [
                'type' => $this->type,
                $this->type => $this->media_id,
                'text' => $this->text,
            ];
        }

        return false;

    }

    /**
     * بررسی می کند آیا این پیام با این دستور است
     * 
     * اگر پیامی با / شروع شود، متن جلوی اسلش نام دستور است
     * 
     * @param string $command نام دستور
     * @param boolean $ignoreCase صرف نظر از بزرگی و کوچکی حروف
     * @return boolean
     */
    public function isCommand(string $command, bool $ignoreCase = true){

        if($this->command === null)
            return false;

        if($ignoreCase){

            if($this->commandLower === null)
                $this->commandLower = strtolower($this->command);

            return strtolower($command) == $this->commandLower;
            
        }
        else{
            return $command == $this->command;
        }

    }
    
	/**
	 * گرفتن آیدی پیام
	 *
	 * @return int
	 */
	function IMsgID() {

        return $this->id;

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
	 * گرفتن آیدی چت
	 *
	 * @return int
	 */
	function IChatID() {

        return $this->chat->IChatID();
        
	}
    
}

