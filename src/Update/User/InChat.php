<?php

namespace Mds\Mmb\Update\User; #auto

use Mds\Mmb\Mmb;
use Mds\Mmb\MmbBase;
use Mds\Mmb\Update\Chat\Member;

class InChat extends MmbBase implements \Mds\Mmb\Update\Interfaces\IChatID, \Mds\Mmb\Update\Interfaces\IUserID
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
     * کاربر هدف
     *
     * @var mixed
     */
    private $user;
    /**
     * چت هدف
     *
     * @var mixed
     */
    private $chat;
   public function __construct($user, $chat, $base){

        if($base->loading_update && !static::$this)
            self::$this = $this;

        $this->_base = $base;
        $this->user = $user;
        if($user instanceof \Mds\Mmb\Update\Interfaces\IUserID)
            $this->user = $user->IUserID();
        $this->chat = $chat;
        if($chat instanceof \Mds\Mmb\Update\Interfaces\IChatID)
            $this->chat = $chat->IChatID();

    }
    
    /**
     * گرفتن اطلاعات کاربر در چت
     *
     * @return Member
     */
   public function getMember(){
        return $this->_base->getChatMember($this->chat, $this->user);
    }
    
    /**
     * حذف کاربر از گروه|کانال
     *
     * @param int $until
     * @return bool
     */
   public function kick($until = null){
        return $this->_base->kick($this->chat, $this->user, $until);
    }
    /**
     * حذف کاربر از گروه|کانال
     *
     * @param int $until
     * @return bool
     */
   public function ban($until = null){
        return $this->_base->ban($this->chat, $this->user, $until);
    }
    
    /**
     * رفع مسدودیت کاربر در گروه|کانال
     *
     * @return bool
     */
   public function unban(){
        return $this->_base->unban($this->chat, $this->user);
    }
    
    /**
     * محدود کردن کاربر
     *
     * @param array $per
     * @param int $until
     * @return bool
     */
   public function restrict($per = [], $until = null){
        return $this->_base->restrict($this->chat, $this->user, $per, $until);
    }
    
    /**
     * ترفیع دادن به کاربر
     *
     * @param array $per
     * @return bool
     */
   public function promote($per = []){
        return $this->_base->promote($this->chat, $this->user, $per);
    }

	/**
	 * گرفتن آیدی چت
	 *
	 * @return int
	 */
	function IChatID() {

        return $this->chat;

	}
	
	/**
	 * گرفتن آیدی کاربر
	 *
	 * @return int
	 */
	function IUserID() {

        return $this->user;
        
	}
}