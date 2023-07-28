<?php

namespace Mmb\Update\User; #auto

use Mmb\MmbBase;
use Mmb\Mmb;
use Mmb\Update\Chat\Member;
use Mmb\Update\Message\Msg;

class UserInfo extends MmbBase implements \Mmb\Update\Interfaces\IChatID, \Mmb\Update\Interfaces\IUserID
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
     * آیدی کاربر
     *
     * @var int
     */
    public $id;
    /**
     * نام کوچک
     *
     * @var string
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
     * یوزرنیم
     *
     * @var string
     */
    public $username;
    /**
     * ربات بودن شخص
     *
     * @var bool
     */
    public $isBot;
    /**
     * کد زبان
     *
     * @var string
     */
    public $lang;
    function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        if($this->_base->loading_update && !static::$this)
            self::$this = $this;

        $this->initFrom($args, [
            'id' => 'id',
            'first_name' => 'firstName',
            'last_name' => 'lastName',
            'username' => 'username',
            'is_bot' => 'isBot',
            'language_code' => 'lang',
        ]);
        $this->name = $this->firstName . ($this->lastName ? " " . $this->lastName : "");
    }
    
    /**
     * گرفتن تصاویر پروفایل کاربر
     *
     * @param int $offset
     * @param int $limit
     * @return Profiles|null
     */
    function getProfs($offset = null, $limit = null)
    {
        return $this->_base->getUserProfs($this->id, $offset, $limit);
    }
    
    /**
     * گرفتن وضعیت کاربر در چت
     *
     * @param mixed $chat
     * @return Member|bool
     */
    public function getMember($chat)
    {
        if(!is_array($chat))
            $chat = ['chat' => $chat];
        $chat['user'] = $this->id;
        return $this->_base->getChatMember($chat);
    }

    /**
     * ارسال پیام به کاربر
     *
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
    function sendMsg($text, $args = [])
    {
        if(gettype($text) == "array"){
            $args = array_merge($text, $args);
        }else{
            $args['text'] = $text;
        }
        $args['id'] = $this->id;
        return $this->_base->sendMsg($args);
    }
    
    /**
     * ارسال پیام به کاربر با ارسال پیامی با نوع دلخواه
     *
     * @param string|array $type
     * @param array $args
     * @return Msg|false
     */
    function send($type, $args=[]){
        if(gettype($type) == "array"){
            $args = array_merge($type, $args);
            $type = @$args['type'];
            unset($args['type']);
        }
        $args['id'] = $this->id;
        return $this->_base->send($type, $args);
    }

	/**
	 * گرفتن آیدی چت
	 *
	 * @return int
	 */
	function IChatID()
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
        return $this->id;
	}
    
    /**
     * یک شی کاربر با این آیدی می سازد تا بتوانید از متد های آن استفاده کنید
     * 
     * این متد اطلاعات کاربر را از تلگرام نمی خواند
     *
     * @param string|integer $id
     * @param ?Mmb $mmb
     * @return UserInfo
     */
    public static function of(string|int $id, ?Mmb $mmb = null)
    {
        return new UserInfo([ 'id' => $id ], $mmb ?? mmb());
    }

}
