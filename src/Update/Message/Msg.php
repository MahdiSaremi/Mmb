<?php

// Copyright (C): t.me/MMBlib

namespace Mmb\Update\Message; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\Chat\Chat;
use Mmb\Update\Chat\ChatShared;
use Mmb\Update\Interfaces\IChatID;
use Mmb\Update\Interfaces\IMsgID;
use Mmb\Update\Message\Data\Contact;
use Mmb\Update\Message\Data\Dice;
use Mmb\Update\Message\Data\Entity;
use Mmb\Update\Message\Data\Location;
use Mmb\Update\Message\Data\Media;
use Mmb\Update\Message\Data\Poll;
use Mmb\Update\Message\Data\Sticker;
use Mmb\Update\User\InChat;
use Mmb\Update\User\UserInfo;
use Mmb\Update\User\UserShared;

class Msg extends MmbBase implements \Mmb\Update\Interfaces\IMsgID, \Mmb\Update\Interfaces\IUserID, \Mmb\Update\Interfaces\IChatID
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
     * مقدار های قابل قبول کد استارت(به صورت کد ریجکس)
     * 
     * @var string $acceptStartCode
     */
    public static $acceptStartCode = '^\s\n\r';
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
     * @var UserInfo[]|null
     */
    public $newMembers;
    /**
     * عضو ترک شده
     *
     * @var UserInfo|null
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
     * @var UserInfo|null
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
     * @var UserInfo|null
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
     * @var UserInfo|null
     */
    public $via;
    /**
     * کاربر در چت
     *
     * @var InChat|null
     */
    public $userInChat;

    /**
     * کاربر به اشتراک گذاشته شده
     *
     * @var UserShared|null
     */
    public $userShared;

    /**
     * چت به اشتراک گذاشته شده
     *
     * @var ChatShared|null
     */
    public $chatShared;

    /**
     * آیا این مدیا اسپویلر دارد
     *
     * @var boolean
     */
    public $hasMediaSpoiler;

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

    function __construct(array $args, ?Mmb $mmb = null, $isInline = false, $inlineID = null)
    {
        parent::__construct($args, $mmb);

        if($this->_base->loading_update && !static::$this)
            self::$this = $this;
        $mmb = $this->_base;

        if($isInline){
            $this->isInline = true;
            $this->inlineID = $inlineID;
            return;
        }

        $this->isInline = false;
        $this->started = false;

        $this->initFrom($args, [
            'message_id' => 'id',
            'chat' => fn($chat) => $this->chat = new Chat($chat, $this->_base),
            'from' => fn($from) => $this->from = new UserInfo($from, $this->_base),
        ]);
        
        // Text
        if(isset($args['text']))
        {
            $this->text = $args['text'];
            $this->type = "text";

            // Command
            if(@$this->text[0] == "/")
            {

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
        elseif(isset($args['caption']))
        {
            $this->text = $args['caption'];
        }


        if(isset($args['photo'])){
            $this->type = "photo";
            $this->photo = [];
            foreach($args['photo'] as $a){
                $this->photo[] = new Media("photo", $a, $mmb);
            }
            $this->media = end($this->photo);
            $this->media_id = $this->media->id;
        }
        elseif(isset($args['voice'])){
            $this->type = "voice";
            $this->media = new Media("voice", $args['voice'], $mmb);
            $this->media_id = $this->media->id;
            $this->voice = $this->media;
        }
        elseif(isset($args['video'])){
            $this->type = "video";
            $this->media = new Media("video", $args['video'], $mmb);
            $this->media_id = $this->media->id;
            $this->video = $this->media;
        }
        elseif(isset($args['animation'])){
            $this->type = "anim";
            $this->media = new Media("anim", $args['animation'], $mmb);
            $this->media_id = $this->media->id;
            $this->anim = $this->media;
        }
        elseif(isset($args['audio'])){
            $this->type = "audio";
            $this->media = new Media("audio", $args['audio'], $mmb);
            $this->media_id = $this->media->id;
            $this->audio = $this->media;
        }
        elseif(isset($args['video_note'])){
            $this->type = "video_note";
            $this->media = new Media("videonote", $args['video_note'], $mmb);
            $this->media_id = $this->media->id;
            $this->videoNote = $this->media;
        }
        elseif(isset($args['location'])){
            $this->type = "location";
            $this->location = new Location($args['location'], $mmb);
        }
        elseif(isset($args['dice'])){
            $this->type = "dice";
            $this->dice = new Dice($args['dice'], $mmb);
        }
        elseif(isset($args['poll'])){
            $this->type = "poll";
            $this->poll = new Poll($args['poll'], $mmb);
        }
        elseif(isset($args['sticker'])){
            $this->type = "sticker";
            $this->media = new Sticker($args['sticker'], $mmb);
            $this->media_id = $this->media->id;
            $this->sticker = $this->media;
        }
        elseif(isset($args['contact'])){
            $this->type = "contact";
            $this->contact = new Contact($args['contact'], $mmb);
        }
        elseif(isset($args['new_chat_members'])){
            $this->type = "new_members";
            $this->newMembers = [];
            foreach($args['new_chat_members']as$once)
                $this->newMembers[] = new UserInfo($once, $mmb);
        }
        elseif(isset($args['left_chat_member'])){
            $this->type = "left_member";
            $this->leftMember = new UserInfo($args['left_chat_member'], $mmb);
        }
        elseif(isset($args['new_chat_title'])){
            $this->type = "new_title";
            $this->newTitle = $args['new_chat_title'];
        }
        elseif(isset($args['new_chat_photo'])){
            $this->type = "new_photo";
            $this->newPhoto = [];
            foreach($args['new_chat_photo'] as $once)
                $this->newPhoto[] = new Media("photo", $once, $mmb);
        }
        elseif(isset($args['delete_chat_photo'])){
            $this->type = "del_photo";
            $this->delPhoto = true;
        }
        elseif(isset($args['group_chat_created'])){
            $this->type = "new_group";
            $this->newGroup = true;
        }
        elseif(isset($args['supergroup_chat_created'])){
            $this->type = "new_supergroup";
            $this->newSupergroup = true;
        }
        elseif(isset($args['channel_chat_created'])){
            $this->type = "new_channel";
            $this->newChannel = true;
        }
        elseif(isset($args['user_shared']))
        {
            $this->type = 'user_shared';
            $this->userShared = new UserShared($args['user_shared'], $mmb);
        }
        elseif(isset($args['chat_shared']))
        {
            $this->type = 'chat_shared';
            $this->chatShared = new ChatShared($args['chat_shared'], $mmb);
        }
        if(isset($args['document'])){
            if(!$this->type){
                $this->type = "doc";
            }
            if(!$this->media){
                $this->media = new Media("doc", $args['document'], $mmb);
                $this->media_id = $this->media->id;
            }
            $this->doc = $this->media;
        }
        if(isset($args['reply_to_message'])){
            $this->reply = new Msg($args['reply_to_message'], $mmb);
        }

        // Time & Date
        $this->date = @$args['date'];
        $this->edited = isset($args['edit_date']);
        if($this->edited)
            $this->editDate = $args['edit_date'];

        // Forward info
        if(isset($args['forward_from'])){
            $this->forwarded = true;
            $this->forwardFrom = new UserInfo($args['forward_from'], $mmb);
        }
        elseif(isset($args['forward_from_chat'])){
            $this->forwarded = true;
            $this->forwardChat = new Chat($args['forward_from_chat'], $mmb);
            $this->forwardMsgId = $args['forward_from_message_id'];
            $this->forwardSig = @$args['forward_signature'];
        }
        else{
            $this->forwarded = false;
        }
        if($this->forwarded)
            $this->forwardDate = @$args['forward_date'];

        // Entity
        if(isset($args['entities']))
            $e = $args['entities'];
        elseif(isset($args['caption_entities']))
            $e = $args['caption_entities'];
        else
            $e = [];
        $this->entities = [];
        foreach($e as $once)
            $this->entities[] = new Entity($once, $mmb);
            
        if(isset($args['pinned_message'])){
            $this->pinnedMsg = new Msg($args['pinned_message'], $mmb);
        }
        if(isset($args['reply_markup'])){
            try{
                $this->key = filterArray3D($args['reply_markup'], ['text'=>"text", 'callback_data'=>"data", 'url'=>"url", 'login_url'=>"login"],null);
            }
            catch(\Exception $e){
                $this->key = null;
            }
        }
        if($this->chat && $this->from && $this->chat->id != $this->from->id){
            $this->userInChat = new InChat($this->from, $this->chat, $mmb);
        }
        if($_ = @$args['via_bot'])
            $this->via = new UserInfo($_, $mmb);
        if($_ = @$args['sender_chat'])
            $this->sender = new Chat($_, $mmb);
        if($_ = @$args['media_group_id'])
            $this->mediaGroupID = $_;
        if($_ = @$args['has_media_spoiler'])
            $this->hasMediaSpoiler = $_;
            
    }

    /**
     * یک شی از نوع پیام می سازد که از آن صرفا برای متد هایی که به آیدی چت و پیام نیاز دارند استفاده کنید
     *
     * @param mixed $chatid
     * @param mixed $msgid
     * @return Msg
     */
    public static function of($chatid, $msgid)
    {
        if($chatid instanceof IChatID)
            $chatid = $chatid->IChatID();
        if($msgid instanceof IMsgID)
            $msgid = $msgid->IMsgID();
        
        return new Msg([
            'id' => $msgid,
            'chat' => [
                'id' => $chatid,
            ]
        ]);
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
     * @return boolean
     */
    public function del(array $args = [])
    {
        $args = maybeArray([
            'chat' => $this->chat->id,
            'msg' => $this->id,
            'args' => $args,
        ]);

        return $this->_base->call('deletemessage', $args);
    }

    /**
     * حذف پیام
     *
     * @param array $args
     * @return boolean
     */
    public function delete(array $args = [])
    {
        return $this->del($args);
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
     * متن پیام را بصورت اچ تی ام ال می دهد
     * 
     * با این کار، برجستگی های پیام نیز در خروجی نمایش داده می شوند
     * 
     * توجه: این تابع در حال توسعه ست و دارای مشکل است
     *
     * @return string|null
     */
    public function getHtml()
    {
        if(!$this->entities)
        {
            return $this->text;
        }

        $chars = mb_str_split($this->text);
        foreach(array_reverse($this->entities) as $entity)
        {
            $start = $entity->offset;
            $end = $start + $entity->len;

            $chars[$start] = match($entity->type)
            {
                'text_link' => "<a href='{$entity->url}'>",
                'bold' => "<b>",
                'italic' => "<i>",
                'underline' => "<u>",
                'strikethrough' => "<s>",
                'spoiler' => "<tg-spoiler>",
                'code' => "<code>",
                'pre' => "<pre>",
                'text_mention' => "<a href='tg://user?id={$entity->user->id}'>",
                default => ''
            } . @$chars[$start];
            @$chars[$end - 1] .= match($entity->type)
            {
                'text_link' => "</a>",
                'bold' => "</b>",
                'italic' => "</i>",
                'underline' => "</u>",
                'strikethrough' => "</s>",
                'spoiler' => "</tg-spoiler>",
                'code' => "</code>",
                'pre' => "</pre>",
                'text_mention' => "</a>",
                default => ''
            };
        }
        
        return implode('', $chars);
    }
    
	/**
	 * گرفتن آیدی پیام
	 *
	 * @return int
	 */
	function IMsgID()
    {
        return $this->id;
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
	 * گرفتن آیدی چت
	 *
	 * @return int
	 */
	function IChatID()
    {
        return $this->chat->IChatID();
	}
    
}

