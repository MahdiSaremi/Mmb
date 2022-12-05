<?php

// Copyright (C): t.me/MMBlib

namespace Mmb\Update; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\Callback\Callback;
use Mmb\Update\Chat\JoinReq;
use Mmb\Update\Chat\MemberUpd;
use Mmb\Update\Inline\ChosenInline;
use Mmb\Update\Inline\Inline;
use Mmb\Update\Message\Data\Poll;
use Mmb\Update\Message\Msg;
use Mmb\Update\Message\PollAnswer;

class Upd extends MmbBase implements Interfaces\ICallbackID, Interfaces\IMsgID, Interfaces\IInlineID, Interfaces\IUserID, Interfaces\IChatID {
    
    /**
     * شی اصلی این کلاس
     * 
     * @var static
     */
    public static $this;


    /**
     * @var array
     */
    private $_real;
    /**
     * @var Mmb
     */
    private $_base;
    /**
     * آیدی عددی آپدیت
     *
     * @var int
     */
    public $id;
    /**
     * پیام
     *
     * @var Msg|null
     */
    public $msg;
    /**
     * پیام ادیت شده
     *
     * @var Msg|null
     */
    public $editedMsg;
    /**
     * کالبک (کلیک بر روی دکمه شیشه ای)
     *
     * @var Callback|null
     */
    public $callback;
    /**
     * اینلاین کوئری (تایپ @ربات_شما ...)
     *
     * @var Inline|null
     */
    public $inline;
    /**
     * پست کانال
     *
     * @var Msg|null
     */
    public $post;
    /**
     * پست ویرایش شده کانال
     *
     * @var Msg|null
     */
    public $editedPost;
    /**
     * انتخاب نتیجه اینلاین توسط کاربر
     *
     * @var ChosenInline|null
     */
    public $chosenInline;
    /**
     * وضعیت جدید نظرسنجی
     *
     * @var Poll|null
     */
    public $poll;
    /**
     * پاسخ جدید نظرسنجی - برای نظرسنجی های غیر ناشناس
     *
     * @var PollAnswer|null
     */
    public $pollAnswer;
    /**
     * وضعیت جدید کاربر در چت خصوصی - مانند توقف ربات
     *
     * @var MemberUpd|null
     */
    public $myChatMember;
    /**
     * وضعیت جدید کاربر در چت - مانند بن شدن
     *
     * @var MemberUpd|null
     */
    public $chatMember;
    /**
     * درخواست جدید عضویت
     *
     * @var JoinReq|null
     */
    public $joinReq;
    
    function __construct($upd, Mmb $base){

        if($base->loading_update && !static::$this)
            self::$this = $this;

        $this->_real = $upd;
        $this->_base = $base;
        $this->id = $upd['update_id'];
        if(isset($upd['message'])){
            $this->msg = new Msg($upd['message'], $base);
        }
        elseif(isset($upd['edited_message'])){
            $this->editedMsg = new Msg($upd['edited_message'], $base);
        }
        elseif(isset($upd['callback_query'])){
            $this->callback = new Callback($upd['callback_query'], $base);
        }
        elseif(isset($upd['inline_query'])){
            $this->inline = new Inline($upd['inline_query'], $base);
        }
        elseif(isset($upd['channel_post'])){
            $this->post = new Msg($upd['channel_post'], $base);
        }
        elseif(isset($upd['edited_channel_post'])){
            $this->editedPost = new Msg($upd['edited_channel_post'], $base);
        }
        elseif(isset($upd['chosen_inline_result'])){
            $this->chosenInline = new ChosenInline($upd['chosen_inline_result'], $base);
        }
        elseif(isset($upd['poll'])){
            $this->poll = new Poll($upd['poll'], $base);
        }
        elseif(isset($upd['poll_answer'])){
            $this->pollAnswer = new PollAnswer($upd['poll_answer'], $base);
        }
        elseif(isset($upd['my_chat_member'])){
            $this->myChatMember = new MemberUpd($upd['my_chat_member'], $base);
        }
        elseif(isset($upd['chat_member'])){
            $this->chatMember = new MemberUpd($upd['chat_member'], $base);
        }
        elseif(isset($upd['chat_join_request'])){
            $this->joinReq = new JoinReq($upd['chat_join_request'], $base);
        }
        if(!self::$this){
            self::$this = $this;
        }
        
    }
    
    /**
     * دریافت آپدیت دریافتی واقعی
     *
     * @return array
     */
    public function real() {

        $real = $this->_real;
        settype($real, "array");
        return $real;

    }



	/**
	 * گرفتن آیدی پیام
	 *
	 * @return int
	 */
	function ICallbackID() {
        
        return $this->callback ? $this->callback->ICallbackID() : 0;

	}
	
	/**
	 * گرفتن آیدی پیام
	 *
	 * @return int
	 */
	function IMsgID() {

        if($this->msg)
            return $this->msg->IMsgID();

        if($this->editedMsg)
            return $this->editedMsg->IMsgID();
            
        return 0;

	}
	
	/**
	 * گرفتن آیدی کاربر
	 *
	 * @return int
	 */
	function IUserID() {

        if($this->msg)
            return $this->msg->IUserID();

        if($this->editedMsg)
            return $this->editedMsg->IUserID();

        if($this->callback)
            return $this->callback->IUserID();

        if($this->inline)
            return $this->inline->IUserID();

        if($this->chosenInline)
            return $this->chosenInline->IUserID();

        if($this->joinReq)
            return $this->joinReq->IUserID();

        if($this->chatMember)
            return $this->chatMember->IUserID();

        if($this->pollAnswer)
            return $this->pollAnswer->IUserID();

        return 0;

	}
	
	/**
	 * گرفتن آیدی چت
	 *
	 * @return int
	 */
	function IChatID() {

        if($this->msg)
            return $this->msg->IChatID();

        if($this->editedMsg)
            return $this->editedMsg->IChatID();

        if($this->callback)
            return $this->callback->IChatID();

        if($this->joinReq)
            return $this->joinReq->IChatID();

        if($this->chatMember)
            return $this->chatMember->IChatID();

        return 0;

	}

	/**
	 * گرفتن آیدی پیام
	 *
	 * @return int
	 */
	function IInlineID() {

        return $this->inline ? $this->inline->IInlineID() : 0;

	}

}
