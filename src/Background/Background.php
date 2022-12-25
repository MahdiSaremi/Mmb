<?php

// Copyright (C): t.me/MMBlib

namespace Mmb\Background; #auto

use Opis\Closure\SerializableClosure;

class Background {

    /**
     * اتصال سرور و مرورگر را می بندد
     * 
     * این ویژگی فقط برای صفحات وب و کلاینت هایی که پشتیبانی می کنند کار می کند
     *
     * @return void
     */
    public static function closeConnection() {

        header("HTTP/1.0 200 OK", true, 200);
        header("Connection: close");
        header("Content-type: text/html; charset=UTF-8");
        ob_end_clean();
        ignore_user_abort(true);
        ob_start();
        echo('Background...');
        $size = ob_get_length();
        header("Content-Length: $size");
        ob_end_flush();
        @ob_flush();
        flush();
        if (session_id()) session_write_close();
        if(function_exists('fastcgi_finish_request')) fastcgi_finish_request();

    }

    /**
     * لینکی را باز می کند و بعد از دریافت هدر اتصال را قطع می کند
     *
     * @param string $url
     * @return void
     */
    public static function openAndBreak($url) {

        fclose(fopen($url, "r"));

    }

    /**
     * گرفتن لینک فعلی
     *
     * @return string
     */
    public static function getCurrentUrl() {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
                . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    
    /**
     * اجرای دستور بدون انتظار برای پاسخ
     * 
     * این ویژگی فقط برای لینوکس کار می کند و نیاز به دسترسی ترمینال دارد
     *
     * @param string $command
     * @return void
     */
    public static function exec($command) {
        
        exec("$command >/dev/null 2>&1 &");
        
    }

    /**
     * اجرای فایل پی.اچ.پی با کامند بدون انتظار برای پاسخ
     * 
     * این ویژگی فقط برای لینوکس کار می کند و نیاز به دسترسی ترمینال دارد
     *
     * @param string $path
     * @return void
     */
    public static function execPhp($path) {

        static::exec("php \"" . addslashes($path) . "\"");

    }

    /**
     * شبیه سازی اجرای پس زمینه
     * 
     * تابعی که وارد می کنید را دوباره به فایل ربات ارسال می کند تا در پس زمینه اجرا شود.
     * با این کار مقدار های یوز شده جابجا می شود، اما دیگر نه موقعیت و نه هیچکدام از متغیر ها و کلاس ها دیگر وجود ندارد
     *
     * @param Callable|\Closure $callback
     * @return void
     */
    public static function run($callback) {

        $id = md5(time() . rand(1, 10000));
        BackgroundStorage::set('closures.' . $id, serializeClosure($callback));
        $url = static::getCurrentUrl() . "?" . http_build_query([
            'atom' => 'background',
            'background' => 'closure',
            'id' => $id,
        ]);
        self::openAndBreak($url);

    }

    /**
     * اجرا کردن کلاسی در پس زمینه
     *
     * @param Task|string $class
     * @return void
     */
    public static function runTask($class) {

        if(is_string($class) && method_exists($class, 'instance'))
            $class = $class::instance();

        if(!($class instanceof Task))
            throw new \Mmb\Exceptions\TypeException("Background::runTask() : Class '\Mmb\Background\Task' required, '".(get_class($class) ?: gettype($class))."' given.");

        $id = md5(time() . rand(1, 10000));
        BackgroundStorage::set('tasks.' . $id, serialize($class));
        $url = static::getCurrentUrl() . "?" . http_build_query([
            'atom' => 'background',
            'background' => 'task',
            'id' => $id,
        ]);
        self::openAndBreak($url);

    }


}
