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

    function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        $this->initFrom($args, [
            'type' => 'type',
            'offset' => 'offset',
            'length' => 'len',
            'language' => 'lang',
        ]);

        switch($this->type)
        {
            case 'text_link':
                $this->url = @$args['url'];
                break;
            case 'text_mention':
                $this->user = new UserInfo(@$args['user'], $this->_base);
                break;
        }
    }

}
