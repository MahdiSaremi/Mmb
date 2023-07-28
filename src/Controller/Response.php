<?php
#auto-name
namespace Mmb\Controller;

use Closure;
use Mmb\Update\Message\Msg;

class Response
{

    protected static $method;

    /**
     * تنظیم متد پاسخگویی
     * 
     * Example: `Response::setResponse('reply');`
     *
     * @param string|Closure|callable $callback
     * @return void
     */
    public static function setResponse($callback, $message = null)
    {
        static::$method = $callback;
        if($message)
            static::setMessage($message);
    }

    protected static $message;

    /**
     * تنظیم پیام پاسخگویی برای اولین پاسخ
     *
     * @param string|array $message
     * @return void
     */
    public static function setMessage($message)
    {
        static::$message = $message;
    }

    /**
     * ارسال پاسخ
     *
     * @param string|array $text
     * @param array $args
     * @return Msg|false|mixed
     */
    public static function response($text, array $args = [])
    {
        $args = maybeArray([
            'type' => 'text',
            'text' => $text,
            '_' => $args,
        ]);
        if(static::$message)
        {
            $args = array_replace($args, is_array(static::$message) ? static::$message : [ 'text' => static::$message ]);
            static::$message = null;
        }

        return static::responseIt($args);
    }

    /**
     * ارسال پاسخ - بدون دستکاری شدن متن
     *
     * @param string|array $text
     * @param array $args
     * @return Msg|false|mixed
     */
    public static function responseIt($text, array $args = [])
    {
        $method = static::$method;
        $args = maybeArray([
            'type' => 'text',
            'text' => $text,
            '_' => $args,
        ]);
        if(!$method)
        {
            return static::defaultResponse($args);
        }
        else
        {
            return $method($args);
        }
    }

    /**
     * متد پیشفرض پاسخگویی
     *
     * @param array $args
     * @return Msg|false|mixed
     */
    public static function defaultResponse(array $args)
    {
        return reply($args);
    }
    
}
