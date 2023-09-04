<?php

namespace Mmb\Lang; #auto

use Closure;
use Exception;
use Mmb\Exceptions\TypeException;
use Mmb\Tools\Advanced;
use Mmb\Tools\AdvancedValue;
use Mmb\Tools\ATool;

class Lang
{

    private static $path = [];
    private static $cacheLoad = [];

    public static function resetCache()
    {
        static::$cacheLoad = [];
    }

    /**
     * لود کردن پوشه زبان ها
     * 
     * @param string $path
     * @return void
     */
    public static function loadLangFrom($path)
    {
        self::$path[] = $path;
    }

    private static $lang = 'en';

    /**
     * تنظیم زبان اصلی
     * 
     * @param string $lang
     * @return void
     */
    public static function setLang($lang)
    {
        self::$lang = $lang;
    }

    /**
     * گرفتن زبان اصلی
     * 
     * @return string
     */
    public static function getLang()
    {
        return self::$lang;
    }

    /**
     * تغییر زبان برای کالبک مورد نظر
     *
     * @param string $lang
     * @param Closure $callback `fn()`
     * @return void
     */
    public static function changeLang($lang, Closure $callback)
    {
        $oldLang = static::getLang();
        static::setLang($lang);

        $callback();

        static::setLang($oldLang);
    }

    private static $default = 'en';

    public static function setDefault($lang)
    {
        static::$default = $lang;
    }

    public static function getDefault()
    {
        return static::$default;
    }

    /**
     * گرفتن دیتای زبان
     * 
     * @param string $lang
     * @return array
     */
    private static function getLangData($lang)
    {
        if(($data = self::$cacheLoad[$lang] ?? false) !== false)
        {
            return $data;
        }

        $data = [];
        foreach(self::$path as $dir)
        {
            if(file_exists("$dir/$lang.php"))
            {
                $data = ATool::mergeInner($data, includeFile("$dir/$lang.php"));
            }
        }

        return self::$cacheLoad[$lang] = $data;
    }

    public static function convertArgs($args = [], ...$_args)
    {
        if(!is_array($args))
        {
            $args = [$args];
            array_push($args, ...$_args);
        }

        return $args;
    }

    /**
     * گرفتن متن با زبان پیشفرض یا زبان پیشفرض
     * 
     * @param string $name
     * @param array|mixed $args
     * @param mixed ...$_args
     * @return string
     */
    public static function text($name, $args = [], ...$_args)
    {
        return self::textFromLang($name, self::getLang(), $args, ...$_args);
    }
    
    /**
     * گرفتن متن با زبان مورد نظر یا زبان پیشفرض
     * 
     * @param string $name
     * @param string $lang
     * @param array|mixed $args
     * @param mixed ...$_args
     * @return string
     */
    public static function textFromLang($name, $lang, $args = [], ...$_args)
    {
        $args = static::convertArgs($args, ...$_args);

        $result = static::convertFromData(static::getLangData($lang), $lang, $name, $args);

        if(is_null($result) && ($default = static::getDefault()) != $lang)
        {
            $result = static::convertFromData(static::getLangData($default), $lang, $name, $args);
        }

        if(is_null($result))
        {
            throw new LangValueNotFound("Language value '$lang.$name' is not defined");
        }

        return $result;
    }

    /**
     * گرفتن متن بر اساس دیتای زبان
     *
     * @param array $data
     * @param string $lang
     * @param string $name
     * @param array $args
     * @return ?string
     */
    public static function convertFromData(array $data, $lang, $name, $args)
    {
        if(isset($data[$name]))
        {
            return self::convertFromText($data[$name], $lang, $args);
        }
        if($value = ATool::selectorGet($data, $name))
        {
            return self::convertFromText($value, $lang, $args);
        }
    }

    /**
     * گرفتن متن بر اساس ورودی ها
     * 
     * @param string $text
     * @param array $args
     * @return string
     */
    public static function convertFromText($text, $lang, $args)
    {
        if($text instanceof Closure)
        {
            return $text($args);
        }

        if($text instanceof AdvancedValue)
        {
            $text = Advanced::getRealValue($text);
        }
        if(!is_string($text))
        {
            throw new TypeException("Lang value expected string value, given '" . typeOf($text) . "'");
        }

        // --- Functions ---
        // > @{langs.%lang%}?{Unknown %lang%}
        // > lang("langs.$args[lang]") ?: "Unknown $args[lang]"
        $text = preg_replace_callback('/@\{(.*?)\}(\?\{(.*?)\})?/', 
            function ($res) use (&$args, $lang) {
                $name = self::convertFromText($res[1], $lang, $args);
                if(@$res[3])
                    $default = self::convertFromText($res[3], $lang, $args);
                else
                    $default = null;
                return tryLangFrom($name, $lang, []/*$args*/) ?: $default;
            }
        , $text);
        
        // --- Variables ---
        // > Hi %name%
        // > "Hi $args[name]"
        return preg_replace_callback('/%([\w\d_\-\.]*?)%/',
            function ($res) use ($args) {
                return ATool::selectorGet($args, $res[1], $res[0]);
            }
        , $text);
    }

    /**
     * گرفتن متن ترجمه شده به زبان فعلی یا زبان پیشفرض
     * 
     * در صورت پیدا نشدن خطایی بر نمی گرداند
     *
     * @param string $name
     * @param array $args
     * @param mixed ...$_args
     * @return string
     */
    public static function tryText($name, $args = [], ...$_args)
    {
        try
        {
            return static::text($name, $args, ...$_args);
        }
        catch(LangValueNotFound $e)
        {
            return null;
        }
    }
    
    /**
     * گرفتن متن ترجمه شده به زبان مورد نظر یا زبان پیشفرض
     * 
     * در صورت پیدا نشدن خطایی بر نمی گرداند
     *
     * @param string $name
     * @param string $lang
     * @param array $args
     * @param mixed ...$_args
     * @return string
     */
    public static function tryTextFrom($name, $lang, $args = [], ...$_args)
    {
        try
        {
            return static::textFromLang($name, $lang, $args, ...$_args);
        }
        catch(LangValueNotFound $e)
        {
            return null;
        }
    }

    /**
     * گرفتن متن ترجمه شده به زبان فعلی
     * 
     * در صورت عدم وجود خطا بر می گرداند
     * 
     * @throws LangValueNotFound
     *
     * @param string $name
     * @param array $args
     * @param mixed ...$_args
     * @return string
     */
    public static function get($name, $args = [], ...$_args)
    {
        return static::getFrom($name, static::getLang(), $args, ...$_args);
    }
    
    /**
     * گرفتن متن ترجمه شده به زبان مورد نظر
     * 
     * در صورت عدم وجود خطا بر می گرداند
     * 
     * @throws LangValueNotFound
     *
     * @param string $name
     * @param string $lang
     * @param array $args
     * @param mixed ...$_args
     * @return string
     */
    public static function getFrom($name, $lang, $args = [], ...$_args)
    {
        $args = static::convertArgs($args, ...$_args);

        $result = static::convertFromData(static::getLangData($lang), $lang, $name, $args);

        if(is_null($result))
        {
            throw new LangValueNotFound("Language value '$lang.$name' is not defined");
        }

        return $result;
    }

    /**
     * گرفتن متن ترجمه شده به زبان فعلی
     * 
     * در صورت پیدا نشدن خطایی بر نمی گرداند
     *
     * @param string $name
     * @param array $args
     * @param mixed ...$_args
     * @return string
     */
    public static function tryGet($name, $args = [], ...$_args)
    {
        try
        {
            return static::get($name, $args, ...$_args);
        }
        catch(LangValueNotFound $e)
        {
            return null;
        }
    }
    
    /**
     * گرفتن متن ترجمه شده به زبان مورد نظر
     * 
     * در صورت پیدا نشدن خطایی بر نمی گرداند
     *
     * @param string $name
     * @param string $lang
     * @param array $args
     * @param mixed ...$_args
     * @return string
     */
    public static function tryGetFrom($name, $lang, $args = [], ...$_args)
    {
        try
        {
            return static::getFrom($name, $lang, $args, ...$_args);
        }
        catch(LangValueNotFound $e)
        {
            return null;
        }
    }

}
