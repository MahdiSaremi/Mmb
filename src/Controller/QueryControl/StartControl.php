<?php
#auto-name
namespace Mmb\Controller\QueryControl;

use Mmb\Exceptions\MmbException;

trait StartControl
{
    
    /**
     * تنظیم الگوی کوئری
     * 
     * @param QueryBooter $booter
     * @return void
     */
    public abstract function bootStart(QueryBooter $booter);


    protected $_start_booter;
    /**
     * گرفتن بوتر استارت
     * 
     * @return QueryBooter
     */
    public function getStartBooter()
    {
        if ($this->_start_booter)
            return $this->_start_booter;

        $this->_start_booter = $booter = new QueryBooter(static::class);
        $this->bootStart($booter);

        return $booter;
    }

    /**
     * گرفتن هندلر استارت
     * 
     * @return StartQueryHandler
     */
    public static function startCommand()
    {
        return new StartQueryHandler(static::class);
    }

    /**
     * ایجاد لینک استارت
     *
     * @param mixed ...$args
     * @return string
     */
    public static function createLink(...$args)
    {
        $username = config('bot.username');
        if(!$username)
            throw new MmbException("Link generator required 'bot.username' config");

        $username = ltrim($username, '@');
        return "https://t.me/$username?start=" . urlencode(app(static::class)->getStartBooter()->makeQuery($args));
    }

    /**
     * ایجاد دکمه شیشه ای با لینک استارت
     * 
     * @param string $text
     * @param mixed ...$args
     * @return array
     */
    public static function keyStart($text, ...$args)
    {
        if(count($args) == 1 && is_array($args[0]))
            $args = $args[0];

        return [
            'text' => $text,
            'url' => static::createLink(...$args),
        ];
    }
    
}
