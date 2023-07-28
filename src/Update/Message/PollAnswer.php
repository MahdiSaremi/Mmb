<?php

namespace Mmb\Update\Message; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Update\User\UserInfo;

class PollAnswer extends MmbBase implements \Mmb\Update\Interfaces\IUserID 
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
     * شناسه نظرسنجی
     *
     * @var string
     */
    public $id;
    /**
     * کاربر رای دهنده
     *
     * @var UserInfo
     */
    public $user;
    /**
     * گزینه های انتخاب شده
     *
     * @var int[]
     */
    public $options;
    /**
     * تعداد انتخاب ها
     *
     * @var int
     */
    public $chosenCount;

    public function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        if($this->_base->loading_update && !static::$this)
            self::$this = $this;

        $this->initFrom($args, [
            'poll_id' => 'id',
            'user' => fn($user) => new UserInfo($user, $this->_base),
            'options' => 'option_ids',
        ]);
        $this->chosenCount = count($this->options ?? []);
    }

    
	/**
	 * گرفتن آیدی کاربر
	 *
	 * @return int
	 */
	function IUserID() {
        
        return $this->user->IUserID();

	}
}
