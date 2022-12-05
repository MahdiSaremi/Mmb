<?php

namespace Mds\Mmb\Files; #auto

use Closure;

class Files
{

    /**
     * باز کردن، ویرایش و ذخیره فایل
     * 
     * این تابع درخواست ها را در صف قرار می دهد تا ویرایشات همزمان کنترل شوند
     *
     * @param string $file
     * @param Closure|Callable|string|array $callback `function($content) { return $newContent; }`
     * @return bool
     */
    public static function editText($file, $callback) {
        // Create if not exists
        if(!file_exists($file))
            touch($file);
        
        // Open
        $f = fopen($file, "r+");
        if(!$f) return false;
        
        self::lock($f);
        $content = stream_get_contents($f);

        // Run
        $content = $callback($content);

        // Close
        fseek($f, 0);
        ftruncate($f, strlen($content));
        fwrite($f, $content);
        self::unlock($f);
        fclose($f);
        return true;
    }

    /**
     * باز کردن، ویرایش و ذخیره فایل
     * 
     * این تابع درخواست ها را در صف قرار می دهد تا ویرایشات همزمان کنترل شوند
     *
     * @param string $file
     * @param Closure|Callable|string|array $callback `function($stream)`
     * @return bool
     */
    public static function editStream($file, $callback) {
        // Create if not exists
        if(!file_exists($file))
            touch($file);

        // Open
        $f = fopen($file, "r+");
        if(!$f) return false;
        
        self::lock($f);

        // Run
        $callback($f);

        // Close
        self::unlock($f);
        fclose($f);
        return true;
    }

    /**
     * خواندن فایل
     *
     * این تابع درخواست ها را در صف قرار می دهد تا از خوانده شدن اطمینان حاصل کند
     * 
     * @param string $file
     * @param integer $maxLen
     * @return string|false
     */
    public static function get($file, $maxLen = -1) {
        if(!file_exists($file))
            return false;
        
        $f = fopen($file, "r");
        if(!$f) return false;
        
        self::lock($f);
        if($maxLen == -1)
            $content = stream_get_contents($f);
        else
            $content = stream_get_contents($f, $maxLen);
        self::unlock($f);
        fclose($f);

        return $content;
    }

    /**
     * نوشتن در فایل
     *
     * این تابع درخواست ها را در صف قرار می دهد تا از نوشته شدن اطمینان حاصل کند
     * 
     * @param string $file
     * @param string $content
     * @return bool
     */
    public static function put($file, $content) {
        // Create if not exists
        if(!file_exists($file))
            touch($file);

        $f = fopen($file, "r+");
        if(!$f) return false;

        self::lock($f);
        ftruncate($f, strlen($content));
        fwrite($f, $content);
        self::unlock($f);
        fclose($f);
        return true;
    }

    /**
     * افزودن به فایل
     *
     * این تابع درخواست ها را در صف قرار می دهد تا از نوشته شدن اطمینان حاصل کند
     * 
     * @param string $file
     * @param string $content
     * @return bool
     */
    public static function append($file, $content) {
        $f = fopen($file, "r+");
        if(!$f) return false;
        
        self::lock($f);
        fseek($f, 0, SEEK_END);
        fwrite($f, $content);
        self::unlock($f);
        fclose($f);
        return true;
    }

    /**
     * قفل کردن فایل
     * 
     * این تابع تا باز شدن فایل صبر می کند
     * * حداکثر تلاش را -1 بگذارید تا بدون محدودیت شود
     *
     * @param resource $f
     * @param integer $maxTry
     * @return void
     */
    public static function lock($f, $maxTry = 10000) {
        if($maxTry == -1) {
            while(!flock($f, LOCK_EX|LOCK_NB));
        }
        else {
            $try = 0;
            while(!flock($f, LOCK_EX|LOCK_NB) && ++$try < $maxTry);
        }
    }

    /**
     * باز کردن فایل
     *
     * @param resource $f
     * @return void
     */
    public static function unlock($f) {
        flock($f, LOCK_UN);
    }

}
