<?php

namespace Mmb\Controller\QueryControl; #auto

trait InlineControl
{

    /**
     * تنظیم الگوی کوئری
     * 
     * @param QueryBooter $booter
     * @return void
     */
    public abstract function bootInline(QueryBooter $booter);


    protected $_inline_booter;
    /**
     * گرفتن بوتر کالبک
     * 
     * @return QueryBooter
     */
    public function getInlineBooter()
    {
        if ($this->_inline_booter)
            return $this->_inline_booter;

        $this->_inline_booter = $booter = new QueryBooter(static::class);
        $this->bootInline($booter);

        return $booter;
    }

    /**
     * گرفتن هندلر کالبک
     * 
     * @return InlineHandler
     */
    public static function inlineQuery()
    {
        return new InlineHandler(static::class);
    }

    /**
     * ایجاد دکمه شیشه ای با کوئری مناسب
     * 
     * @param string $text
     * @param mixed ...$args
     * @return array
     */
    public static function keyShareInline($text, ...$args)
    {
        if(count($args) == 1 && is_array($args[0]))
            $args = $args[0];

        return [
            'text' => $text,
            'inline' => app(static::class)->getInlineBooter()->makeQuery($args),
        ];
    }
    

    /**
     * ایجاد دکمه شیشه ای با کوئری مناسب
     * 
     * @param string $text
     * @param mixed ...$args
     * @return array
     */
    public static function keyShareInlineThis($text, ...$args)
    {
        if(count($args) == 1 && is_array($args[0]))
            $args = $args[0];

        return [
            'text' => $text,
            'inlineThis' => app(static::class)->getInlineBooter()->makeQuery($args),
        ];
    }
    
}
