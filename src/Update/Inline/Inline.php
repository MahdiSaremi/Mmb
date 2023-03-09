<?php

// Copyright (C): t.me/MMBlib

namespace Mmb\Update\Inline; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\User\UserInfo;

class Inline extends MmbBase implements \Mmb\Update\Interfaces\IInlineID, \Mmb\Update\Interfaces\IUserID 
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
     * آیدی اینلاین کوئری
     *
     * @var string
     */
    public $id;
    /**
     * از طرف کاربر
     *
     * @var UserInfo
     */
    public $from;
    /**
     * کوئری
     *
     * @var string
     */
    public $query;
    /**
     * Offset
     * 
     *
     * @var 
     */
    public $offset;
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
     * آیا در چت خصوصی درخواست انجام شده
     *
     * @var bool
     */
    public $isPrivate;
    /**
     * آیا در گروه یا سوپر گروه درخواست انجام شده
     *
     * @var bool
     */
    public $isGroup;
    /**
     * آیا در کانال درخواست انجام شده
     *
     * @var bool
     */
    public $isChannel;

    public function __construct($in, $base) {

        if($base->loading_update && !static::$this)
            self::$this = $this;

        $this->_base = $base;
        $this->id = $in['id'];
        $this->from = new UserInfo($in['from'], $base);
        $this->query = $in['query'];
        $this->offset = $in['offset'];
        $this->type = @$in['type'];
        $this->isPrivate = $this->type == self::TYPE_PRIVATE;
        $this->isGroup = $this->type == self::TYPE_GROUP || $this->type == self::TYPE_SUPERGROUP;
        $this->isChannel = $this->type == self::TYPE_CHANNEL;
        
    }
    
    /**
     * پاسخ به اینلاین کوئری
     *
     * @param array $results
     * @param array $args
     * @return bool
     */
    function answer($results, $args=[]){
        if(isset($results['results']))
            $args = array_merge($results, $args);
        else
            $args['results'] = $results;
        $args['id'] = $this->id;
        return $this->_base->call('answerinlinequery', $args);
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
	 * گرفتن آیدی پیام
	 *
	 * @return int
	 */
	function IInlineID() {

        return $this->id;

	}

}
