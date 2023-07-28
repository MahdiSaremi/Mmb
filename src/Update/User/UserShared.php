<?php
#auto-name
namespace Mmb\Update\User;

use Mmb\Mmb;
use Mmb\MmbBase;

class UserShared extends MmbBase
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
     * آیدی درخواست
     *
     * @var int
     */
    public $id;

    /**
     * آیدی عددی کاربر
     *
     * @var int
     */
    public $userId;

    public function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        if($this->_base->loading_update && !static::$this)
            self::$this = $this;

        $this->initFrom($args, [
            'request_id' => 'id',
            'user_id' => 'userId',
        ]);
    }
    
}
