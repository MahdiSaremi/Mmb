<?php

namespace Mmb\Controller\QueryControl; #auto


trait MsgControl
{

    /**
     * تنظیم الگوی کوئری
     * 
     * @param QueryBooter $booter
     * @return void
     */
    public abstract function bootMsg(QueryBooter $booter);


    protected $_msg_booter;
    /**
     * گرفتن بوتر پیام
     * 
     * @return QueryBooter
     */
    public function getMsgBooter()
    {
        if ($this->_msg_booter)
            return $this->_msg_booter;

        $this->_msg_booter = $booter = new QueryBooter(static::class);
        $this->bootMsg($booter);

        return $booter;
    }

    /**
     * گرفتن هندلر پیام
     * 
     * @return MsgHandler
     */
    public static function msgQuery()
    {
        return new MsgHandler(static::class);
    }

    /**
     * ایجاد دکمه معموی با متن مناسب
     * 
     * @param string $text
     * @param mixed ...$args
     * @return array
     */
    public static function keyNormal(...$args)
    {
        if(count($args) == 1 && is_array($args[0]))
            $args = $args[0];

        return [
            'text' => app(static::class)->getMsgBooter()->makeQuery($args),
        ];
    }
    
}
