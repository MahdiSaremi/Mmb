<?php

namespace Mmb\Update\Message\Data; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\User\UserInfo;

/**
 * برجستگی های متن
 * انکدینگ این کلاس توسط تلگرام UTF-16 می باشد
 */
class Entity extends MmbBase
{

    /**
     * نوع
     *
     * @var string
     */
    public $type;

    /**
     * نقطه شروع
     *
     * @var int
     */
    public $offset;

    /**
     * طول برجستگی
     *
     * @var int
     */
    public $len;

    /**
     * لینک برجستگی
     *
     * @var string
     */
    public $url;

    /**
     * کاربر تگ شده برجستگی
     *
     * @var UserInfo
     */
    public $user;

    /**
     * کد زبان
     *
     * @var string
     */
    public $lang;

    /**
     * @var Mmb
     */
    private $_base;
    function __construct($e, $base){
        $this->_base = $base;
        $this->type = $e['type'];
        $this->offset = $e['offset'];
        $this->len = $e['length'];
        if($this->type == "text_link")
            $this->url = @$e['url'];
        if($this->type == "text_mention")
            $this->user = new UserInfo(@$e['user'], $base);
        $this->lang = @$e['language'];
    }

}
