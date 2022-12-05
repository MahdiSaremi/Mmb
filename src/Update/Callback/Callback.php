<?php

// Copyright (C): t.me/MMBlib

namespace Mds\Mmb\Update\Callback; #auto

use Mds\Mmb\Mmb;
use Mds\Mmb\MmbBase;
use Mds\Mmb\Update\Message\Msg;
use Mds\Mmb\Update\User\User;

class Callback extends MmbBase implements \Mds\Mmb\Update\Interfaces\ICallbackID, \Mds\Mmb\Update\Interfaces\IMsgID, \Mds\Mmb\Update\Interfaces\IUserID, \Mds\Mmb\Update\Interfaces\IChatID
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
     * از طرف کاربر
     *
     * @var User
     */
    public $from;
    /**
     * پیام اصلی، یا پیام فیک(بدون اطلاعات، فقط جهت استفاده از توابع) در حالت اینلاین
     *
     * @var Msg
     */
    public $msg;
    /**
     * دیتای دکمه
     *
     * @var string
     */
    public $data;
    /**
     * آیدی کالبک
     *
     * @var string
     */
    public $id;
    /**
     * آیا پیام مربوط به حالت اینلاین است
     *
     * @var bool
     */
    public $isInline;
    function __construct($cl, Mmb $base){

        if($base->loading_update && !static::$this)
            self::$this = $this;

        $this->_base = $base;
        $this->from = new User($cl['from'], $base);
        $this->data = $cl['data'];
        $this->id = $cl['id'];
        if(isset($cl['message'])){
            $this->msg = new Msg($cl['message'], $base);
            $this->isInline = false;
        }
        if(isset($cl['inline_message_id'])){
            $this->isInline = true;
            $this->msg = new Msg($cl['inline_message_id'], $base, true);
            /*$this->msg->isInline = true;
            $this->msg->inlineID = $cl['inline_message_id'];*/
        }
        
    }
    
    /**
     * پاسخ به کالبک (نمایش پیغام و پایان دادن به انتظار تلگرام)
     * اگر شما از این تابع در کالبک های خود استفاده نکنید، در صورت استفاده ی زیاد از کالبک های ربات شما، تلگرام به شما اخطاری می دهد که پاسخ به کالبک ها بسیار طول می کشد!
     *
     * @param string $text
     * @param bool $alert نمایش پنجره هنگام نمایش 
     * @return bool
     */
    function answer($text = null, $alert = false){
        if(is_array($text)){
            $text['id'] = $this->id;
            return $this->_base->answerCallback($text);
        }
        return $this->_base->answerCallback(['id'=>$this->id, 'text'=>$text, 'alert'=>$text ? $alert : null]);
    }

    
	/**
	 * گرفتن آیدی پیام
	 *
	 * @return int
	 */
	function IMsgID() {
        
        return $this->msg->IMsgID();
        
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

        return $this->msg->IChatID();

	}
	/**
	 * گرفتن آیدی پیام
	 *
	 * @return int
	 */
	function ICallbackID() {

        return $this->id;

	}
}
