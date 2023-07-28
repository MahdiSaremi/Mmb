<?php
#auto-name
namespace Mmb\Update\Chat;

use Mmb\Update\Message\Msg;

/**
 * متد های ساده و پر استفاده برای چت را اضافه می کند
 */
trait HasSimpleChatMethods
{
    
    /**
     * ارسال حالت چت
     *
     * @param mixed $action
     * @return bool
     */
    public function action($action)
    {
        return mmb()->action($this->id, $action);
    }

    /**
     * ارسال پیام به چت
     *
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
    public function sendMsg($text, $args = [])
    {
        $args = maybeArray([
            'chat' => $this->id,
            'text' => $text,
            'args' => $args
        ]);
        return mmb()->sendMsg($args);
    }

    /**
     * ارسال پیام به چت با ارسال پیامی با نوع دلخواه
     *
     * @param string|array $type
     * @param array $args
     * @return Msg|false
     */
    public function send($type, $args = [])
    {
        $args['chat'] = $this->id;
        return mmb()->send($type, $args);
    }

}
