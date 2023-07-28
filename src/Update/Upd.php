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

class Upd extends MmbBase implements Interfaces\ICallbackID, Interfaces\IMsgID, Interfaces\IInlineID, Interfaces\IUserID, Interfaces\IChatID
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
     * @var array
     */
    // private $_real;

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
    
    function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        if($this->_base->loading_update && !static::$this)
            self::$this = $this;
        $base = $this->_base;
            
        // $this->_real = $args;
        $this->id = @$args['update_id'];
        if(isset($args['message']))
        {
            $this->msg = new Msg($args['message'], $base);
        }
        elseif(isset($args['edited_message']))
        {
            $this->editedMsg = new Msg($args['edited_message'], $base);
        }
        elseif(isset($args['callback_query']))
        {
            $this->callback = new Callback($args['callback_query'], $base);
        }
        elseif(isset($args['inline_query']))
        {
            $this->inline = new Inline($args['inline_query'], $base);
        }
        elseif(isset($args['channel_post']))
        {
            $this->post = new Msg($args['channel_post'], $base);
        }
        elseif(isset($args['edited_channel_post']))
        {
            $this->editedPost = new Msg($args['edited_channel_post'], $base);
        }
        elseif(isset($args['chosen_inline_result']))
        {
            $this->chosenInline = new ChosenInline($args['chosen_inline_result'], $base);
        }
        elseif(isset($args['poll']))
        {
            $this->poll = new Poll($args['poll'], $base);
        }
        elseif(isset($args['poll_answer']))
        {
            $this->pollAnswer = new PollAnswer($args['poll_answer'], $base);
        }
        elseif(isset($args['my_chat_member']))
        {
            $this->myChatMember = new MemberUpd($args['my_chat_member'], $base);
        }
        elseif(isset($args['chat_member']))
        {
            $this->chatMember = new MemberUpd($args['chat_member'], $base);
        }
        elseif(isset($args['chat_join_request']))
        {
            $this->joinReq = new JoinReq($args['chat_join_request'], $base);
        }
    }
    
    /**
     * دریافت آپدیت دریافتی واقعی
     *
     * @return array
     */
    public function real()
    {
        // $real = $this->_real;
        // settype($real, "array");
        // return $real;
        return $this->getRealData();
    }



	/**
	 * گرفتن آیدی پیام
	 *
	 * @return int
	 */
	public function ICallbackID()
    {
        return $this->callback ? $this->callback->ICallbackID() : 0;
	}
	
	/**
	 * گرفتن آیدی پیام
	 *
	 * @return int
	 */
	public function IMsgID()
    {
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
	public function IUserID()
    {
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
	public function IChatID()
    {
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
	public function IInlineID()
    {
        return $this->inline ? $this->inline->IInlineID() : 0;
	}

    public static function convertUpdTypes(array $types)
    {
        $res = [];
        foreach($types as $type)
        {
            switch($type)
            {    
                case 'msg':
                    $res[] = 'message';
                break;
                case 'editedMsg':
                    $res[] = 'edited_message';
                break;
                case 'callback':
                    $res[] = 'callback_query';
                break;
                case 'joinReq':
                    $res[] = 'chat_join_request';
                break;
                case 'inline':
                    $res[] = 'inline_query';
                break;
                case 'chosenInline':
                    $res[] = 'chosen_inline_result';
                break;
                case 'chatMember':
                    $res[] = 'chat_member';
                break;
                case 'myChatMember':
                    $res[] = 'my_chat_member';
                break;
                case 'post':
                    $res[] = 'channel_post';
                break;
                case 'editedPost':
                    $res[] = 'edited_channel_post';
                break;
                case 'poll':
                    $res[] = 'poll';
                break;
                case 'pollAnswer':
                    $res[] = 'poll_answer';
                break;
            }
        }
        return $res;
    }

}
