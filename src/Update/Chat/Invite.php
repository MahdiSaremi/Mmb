<?php

namespace Mmb\Update\Chat; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\User\UserInfo;

class Invite extends MmbBase
{
    
    /**
     * @var Mmb
     */
    private $_base;

    /**
     * لینک دعوت
     *
     * @var string
     */
    public $link;

    /**
     * چت - داده ی نا امن
     *
     * @var mixed
     */
    public $chatLink;

    /**
     * سازنده لینک
     *
     * @var UserInfo
     */
    public $creator;

    /**
     * عضویت با تایید
     *
     * @var bool
     */
    public $joinReq;

    /**
     * کلید یکتای اصلی بودن
     *
     * @var bool
     */
    public $primary;

    /**
     * آیا لینک باطل شده است
     *
     * @var bool
     */
    public $revoked;

    /**
     * اسم
     * 
     * @var string
     */
    public $name;

    /**
     * تاریخ انقضا
     *
     * @var int
     */
    public $expire;

    /**
     * محدودیت تعداد
     *
     * @var int
     */
    public $limit;

    /**
     * تعداد کاربران منتظر برای تایید
     *
     * @var int
     */
    public $pendings;

    public function __construct($inv, $chat, $base){
        $this->_base = $base;
        $this->link = $inv['invite_link'];
        $this->chatLink = $chat;
        $this->creator = new UserInfo($inv['creator'], $base);
        $this->primary = $inv['is_primary'];
        $this->revoked = $inv['is_revoked'];
        $this->name = @$inv['name'];
        $this->expire = @$inv['expire_date'];
        $this->limit = @$inv['member_limit'];
        $this->pendings = @$inv['pending_join_request_count'];
    }

    /**
     * ویرایش لینک دعوت
     *
     * @param array $args
     * @return Invite|false
     */
    public function edit($args)
    {
        if(!$args['chat'])
            $args['chat'] = $this->chatLink;
        $args['link'] = $this->link;
        return $this->_base->editInviteLink($args);
    }

    /**
     * ویرایش لینک دعوت
     *
     * @param array $args
     * @return Invite|false
     */
    public function revoke(array $args = [])
    {
        $args = maybeArray([
            'chat' => $this->chatLink,
            'link' => $this->link,
            'args' => $args
        ]);
        return $this->_base->revokeInviteLink($args);
    }
}
