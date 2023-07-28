<?php

// Copyright (C): t.me/MMBlib

namespace Mmb\Update\Callback; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\Message\Msg;
use Mmb\Update\User\UserInfo;

class Callback extends MmbBase implements \Mmb\Update\Interfaces\ICallbackID, \Mmb\Update\Interfaces\IMsgID, \Mmb\Update\Interfaces\IUserID, \Mmb\Update\Interfaces\IChatID
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
     * از طرف کاربر
     *
     * @var UserInfo
     */
    public ?UserInfo $from = null;
    /**
     * پیام اصلی، یا پیام فیک(بدون اطلاعات، فقط جهت استفاده از توابع) در حالت اینلاین
     *
     * @var Msg
     */
    public ?Msg $msg = null;
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
    function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        if($this->_base->loading_update && !static::$this)
            self::$this = $this;

        $this->initFrom($args, [
            'from' => fn($from) => $this->from = new UserInfo($from, $this->_base),
            'data' => 'data',
            'id' => 'id',
        ]);

        if(isset($args['message']))
        {
            $this->msg = new Msg($args['message'], $this->_base);
            $this->isInline = false;
        }
        if(isset($args['inline_message_id']))
        {
            $this->isInline = true;
            $this->msg = new Msg([], $this->_base, true, $args['inline_message_id']);
        }
    }

    /**
     * پاسخ به کالبک (نمایش پیغام و پایان دادن به انتظار تلگرام)
     * اگر شما از این تابع در کالبک های خود استفاده نکنید، در صورت استفاده ی زیاد از کالبک های ربات شما، تلگرام به شما اخطاری می دهد که پاسخ به کالبک ها بسیار طول می کشد!
     *
     * @param string|array $text
     * @param bool $alert نمایش پنجره هنگام نمایش 
     * @return bool
     */
    function answer($text = null, $alert = false)
    {
        if(is_array($text))
        {
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
	function IMsgID()
    {
        return $this->msg->IMsgID();
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
        return $this->msg->IChatID();
	}
	/**
	 * گرفتن آیدی پیام
	 *
	 * @return string
	 */
	function ICallbackID()
    {
        return $this->id;
	}
}
