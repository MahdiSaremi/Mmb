<?php
#auto-name
namespace Mmb\Update\Chat;

use Mmb\Mmb;
use Mmb\MmbBase;

class ChatShared extends MmbBase
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
     * @var ?int
     */
    public $id;

    /**
     * آیدی عددی چت
     *
     * @var ?int
     */
    public $chatId;

    public function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        if($this->_base->loading_update && !static::$this)
            self::$this = $this;

        $this->initFrom($args, [
            'request_id' => 'id',
            'chat_id' => 'chatId',
        ]);
    }

    /**
     * شی چتی برای چت مورد نظر می سازد و آن را بر می گرداند
     *
     * @return Chat|false
     */
    public function chat()
    {
        if(isset($this->chatId))
            return Chat::of($this->chatId);
        else
            return false;
    }
    
}
