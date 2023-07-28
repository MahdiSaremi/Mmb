<?php

namespace Mmb\Update\Chat; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\User\UserInfo;

class Member extends MmbBase
{
    /**
     * اطلاعات کاربر
     *
     * @var UserInfo
     */
    public $user;
    /**
     * مقام کاربر
     *
     * @var string
     */
    public $status;
    public const STATUS_CREATOR = 'creator';
    public const STATUS_ADMIN = 'administrator';
    public const STATUS_MEMBER = 'member';
    public const STATUS_LEFT = 'left';
    public const STATUS_RESTRICTED = 'restricted';
    public const STATUS_KICKED = 'kicked';

    /**
     * لقب کاربر
     *
     * @var string
     */
    public $title;
    /**
     *
     * @var int
     */
    public $untilDate;
    /**
     * عضویت کاربر
     *
     * @var bool
     */
    public $isJoin;
    /**
     * ادمین بودن کاربر
     *
     * @var bool
     */
    public $isAdmin;
    /**
     * ناشناس بودن کاربر
     *
     * @var bool
     */
    public $isAnonymous;
    /**
     * دسترسی ها، تنها برای ادمین ها و کاربران محدود شده موجود است
     *
     * @var Per
     */
    public $per;
    public function __construct(array $args, ?MMb $mmb = null)
    {
        parent::__construct($args, $mmb);

        $this->initFrom($args, [
            'user' => fn($user) => $this->user = new UserInfo($user, $this->_base),
            'status' => 'status',
            'custom_title' => 'title',
            'until_date' => 'untilDate',
            'is_anonymous' => 'isAnonymous',
        ]);;
        
        $status = $this->status;
        $this->isJoin = $status == "member" || $status == "creator" || $status == "administrator";
        $this->isAdmin = $status == "creator" || $status == "administrator";
        
        if($status == "creator")
        {
            $this->per = new Per('*', $this->isAnonymous, $this->_base);
        }
        elseif($status == 'restricted')
        {
            $this->per = new Per($args, $this->isAnonymous, $this->_base);
        }
    }
}
