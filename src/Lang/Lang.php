<?php

namespace Mmb\Lang; #auto

use Mmb\Tools\ATool;

class Lang
{

    private static $path = [];
    private static $cacheLoad = [];

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
                $data[] = includeFile("$dir/$lang.php");
            }
        }

        return self::$cacheLoad[$lang] = $data;
        
    }

    /**
     * گرفتن متن با زبان پیشفرض
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
     * گرفتن متن با زبان دلخواه
     * 
     * @param string $name
     * @param string $lang
     * @param array|mixed $args
     * @param mixed ...$_args
     * @return string
     */
    public static function textFromLang($name, $lang, $args = [], ...$_args)
    {

        if(!is_array($args))
        {
            $args = [$args];
            array_push($args, ...$_args);
        }

        foreach(self::getLangData($lang) as $data)
        {
            if(isset($data[$name]))
            {
                return self::getText($data[$name], $args);
            }
            if($value = ATool::selectorGet($data, $name))
            {
                return $value;
            }
        }

        return false;

    }

    /**
     * گرفتن متن بر اساس ورودی ها
     * 
     * @param string $text
     * @param array $args
     * @return string
     */
    private static function getText($text, $args)
    {
        
        if($text instanceof \Closure)
        {
            return $text($args);
        }

        // --- Functions ---
        // > @{langs.%lang%}?{Unknown %lang%}
        // > lang("langs.$args[lang]") ?: "Unknown $args[lang]"
        preg_replace_callback('/@\{(.*?)\}(|\?\{(.*?)\})/', 
            function ($res) use (&$args) {
                $name = self::getText($res[1], $args);
                $default = self::getText(@$res[3], $args);
                return lang($name, []/*$args*/) ?: $default;
            }
        , $text);

        // --- Variables ---
        // > Hi %name%
        // > "Hi $args[name]"
        return preg_replace_callback('/%([\w\d_\-\.]*?)%/',
            function ($res) use (&$args) {
                return $args[$res[1]] ?? $res[0];
            }
        , $text);

    }
    
}
