<?php

namespace Mmb\Controller\QueryControl; #auto


trait CallbackControl
{

    /**
     * تنظیم الگوی کوئری
     * 
     * @param QueryBooter $booter
     * @return void
     */
    public abstract function bootCallback(QueryBooter $booter);


    protected $_callback_booter;
    /**
     * گرفتن بوتر کالبک
     * 
     * @return QueryBooter
     */
    public function getCallbackBooter()
    {
        if ($this->_callback_booter)
            return $this->_callback_booter;

        $this->_callback_booter = $booter = new QueryBooter(static::class);
        $this->bootCallback($booter);

        return $booter;
    }

    /**
     * گرفتن هندلر کالبک
     * 
     * @return CallbackHandler
     */
    public static function callbackQuery()
    {
        return new CallbackHandler(static::class);
    }

    /**
     * ایجاد دکمه شیشه ای با کوئری مناسب
     * 
     * @param string $text
     * @param mixed ...$args
     * @return array
     */
    public static function keyInline($text, ...$args)
    {
        if(count($args) == 1 && is_array($args[0]))
            $args = $args[0];

        return [
            'text' => $text,
            'data' => app(static::class)->getCallbackBooter()->makeQuery($args),
        ];
    }
    
}
