<?php

namespace Mds\Mmb\Update\Chat; #auto

use Mds\Mmb\Mmb;
use Mds\Mmb\MmbBase;
use Mds\Mmb\Update\User\User;

class Member extends MmbBase
{
    /**
     * اطلاعات کاربر
     *
     * @var User
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
    /**
     * @var Mmb
     */
    private $_base;
   public function __construct($v, $base){
        $this->_base = $base;
        $this->user = new User($v['user'], $base);
        $s = $this->status = $v['status'];
        $this->title = @$v['custom_title'];
        $this->untilDate = @$v['until_date'];
        $this->isJoin = $s == "member" || $s == "creator" || $s == "administrator";
        $this->isAdmin = $s == "creator" || $s == "administrator";
        $this->isAnonymous = @$v['is_anonymous'];
        
        if($s == "creator"){
            $this->per = new Per('*', $this->isAnonymous, $base);
        }
        elseif($s == 'restricted'){
            $this->per = new Per($v, $this->isAnonymous, $base);
        }
    }
}
