<?php

// Copyright (C): t.me/MMBlib

use Mmb\Assets\Assets;
use Mmb\Controller\Menu;
use Mmb\Controller\MenuBase;
use Mmb\Controller\Response;
use Mmb\Controller\StepHandler\StepHandler;
use Mmb\Debug\Debug;
use Mmb\Exceptions\MmbException;
use Mmb\ExtraThrow\ExtraErrorMessage;
use Mmb\Guard\Guard;
use Mmb\Kernel\Env;
use Mmb\Kernel\Instance;
use Mmb\Kernel\Kernel;
use Mmb\Lang\Lang;
use Mmb\Lang\LangValueNotFound;
use Mmb\Mapping\Arr;
use Mmb\Mapping\Arrayable;
use Mmb\Mapping\Map;
use Mmb\Mmb;
use Mmb\Tools\ATool;
use Mmb\Tools\InlineResult;
use Mmb\Tools\Keys;
use Mmb\Tools\Optional;
use Mmb\Tools\Text;
use Mmb\Tools\Type;
use Mmb\Update\Callback\Callback;
use Mmb\Update\Chat\Chat;
use Mmb\Update\Chat\Per;
use Mmb\Update\Inline\ChosenInline;
use Mmb\Update\Inline\Inline;
use Mmb\Update\Message\Msg;
use Mmb\Update\Upd;
use Mmb\Update\User\UserInfo;

Kernel::$runTime = microtime(true);

if(!function_exists('mkey'))
{
    /**
     * ساخت کیبورد
     *
     * @param array $key دکمه ها
     * @param bool|null $inline اینلاین بودن
     * @param bool $resize ریسایز خودکار
     * @param bool $encode انکد کردن نتیجه
     * @param bool $once کیبورد یکباره
     * @param bool $selective سلکتیو
     * @return string|array
     */
    function mkey($key, $inline=null, $resize=true, $encode=true, $once=false, $selective=false)
    {
        return Keys::makeKey($key, $inline, $resize, $encode, $once, $selective);
    }
}

if(!function_exists('mPers'))
{
    function mPers($ar)
    {
        return Per::makePers($ar);
    }
}

if(!function_exists('mInlineRes'))
{
    function mInlineRes($results)
    {
        return InlineResult::makeResult($results);
    }
}

if(!function_exists('mInlineRes_A'))
{
    function mInlineRes_A($data)
    {
        return InlineResult::makeSingle($data);
    }
}

if(!function_exists('filterArray'))
{
    function filterArray($array, $keys, $vals=null, $delEmpties1 = false)
    {
        return ATool::filterArray($array, $keys, $vals, $delEmpties1);
    }
}

if(!function_exists('filterArray2D'))
{
    function filterArray2D($array, $keys, $vals=null, $delEmpties2 = false, $delEmpties1 = false)
    {
        return ATool::filterArray2D($array, $keys, $vals, $delEmpties2, $delEmpties1);
    }
}

if(!function_exists('filterArray3D'))
{
    function filterArray3D($array, $keys, $vals=null, $delEmpties3 = false, $delEmpties2 = false, $delEmpties1 = false)
    {
        return ATool::filterArray3D($array, $keys, $vals, $delEmpties3, $delEmpties2, $delEmpties1);
    }
}

if(!function_exists('mmb_log'))
{
    function mmb_log($text)
    {
        if(Mmb::$LOG)
            error_log($text, 0);
            //file_put_contents("mmb_log", "\n[" . date("Y/m/d H:i:s") . "] $text", FILE_APPEND);
        return $text;
    }
}

if(!function_exists('mmb_error_throw'))
{
    function mmb_error_throw($des, $must_throw_error = false)
    {
        /*if(Mmb::$LOG)
            mmb_log($des);*/
        if($must_throw_error || Mmb::$HARD_ERROR)
            throw new MmbException($des);
    }
}


if(!function_exists('eqi'))
{
    /**
     * با صرف نظر کردن از بزرگی و کوچکی حروف، دو رشته را با هم مقایسه می کند
     *
     * @param string $value1
     * @param string ...$values
     * @return bool
     */
    function eqi($value1, $value2)
    {
        return strtolower($value1) == strtolower($value2);
    }
}

if(!function_exists('clamp'))
{
    /**
     * محدود کردن عدد در بازه
     * 
     * با کمک این تابع می تواانید رنجی را مشخص کنید تا عدد شما بزرگ تر یا کوچک تر از این رنج نباشند. در نهایت این تابع یا خود عدد، یا حداکثر و یا حداقل را به شما می دهد
     *
     * @param int|float $number
     * @param int|float $min
     * @param int|float $max
     * @return int|float
     */
    function clamp($number, $min, $max)
    {
        if($number > $max) return $max;
        if($number < $min) return $min;
        return $number;
    }
}

if(!function_exists('delDir'))
{
    /**
     * حذف پوشه و محتویات آن
     *
     * @param string $dirPath
     * @return bool
     */
    function delDir($dirPath)
    {
        if(!is_dir($dirPath))
            return false;

        $files = scandir($dirPath);
        foreach($files as $file) {
            if($file == '.' || $file == '..') continue;
            $path = "$dirPath/$file";
            if(is_dir($path))
                delDir($path);
            else
                unlink($path);
        }
        return rmdir($dirPath);
    }
}


if(!function_exists('htmlEncode'))
{
    /**
     * انکد کردن کاراکتر ها برای مد اچ تی ام ال تلگرام
     *
     * @param string $text
     * @return string
     */
    function htmlEncode($text)
    {
        return Text::htmlEncode($text);
    }
}

if(!function_exists('markdownEncode'))
{
    /**
     * انکد کردن کاراکتر ها برای مد مارک داون تلگرام
     *
     * @param string $text
     * @return string
     */
    function markdownEncode($text)
    {
        return Text::markdownEncode($text);
    }
}

if(!function_exists('markdown2Encode'))
{
    /**
     * انکد کردن کاراکتر ها برای مد مارک داون2 تلگرام
     *
     * @param string $text
     * @return string
     */
    function markdown2Encode($text)
    {
        return Text::markdown2Encode($text);
    }
}

if(!function_exists('textUrl'))
{
    /**
     * یک متن لینک دار با استایل مد نظر می سازد
     *
     * @param mixed $url
     * @param mixed $text
     * @param string $mode
     * @return string
     */
    function textUrl($url, $text, string $mode = 'html')
    {
        switch(strtolower($mode))
        {
            case 'html':
                return '<a href="' . htmlEncode($url) . '">' . htmlEncode("$text") . '</a>';
            case 'markdown':
                return '[' . markdownEncode($text) . '](' . markdownEncode($url) . ')';
            case 'markdown2':
                return '[' . markdown2Encode($text) . '](' . markdown2Encode($url) . ')';
            default:
                throw new InvalidArgumentException("Invalid mode '$mode'");
        }
    }
}

if(!function_exists('textMention'))
{
    /**
     * یک متن منشن کاربر می سازد
     *
     * @param mixed $userid
     * @param mixed $text 
     * @param string $mode
     * @return string
     */
    function textMention($userid, $text = null, string $mode = 'html')
    {
        return textUrl('tg://user?id=' . $userid, $text ?? $userid, $mode);
    }
}

if(!function_exists('startsWith'))
{
    /**
     * بررسی می کند رشته اصلی با رشته دیگری شروع می شود یا نه
     *
     * @param string $string
     * @param string $needle
     * @param bool $ignoreCase
     * @return bool
     */
    function startsWith($string, $needle, $ignoreCase = false)
    {
        return Text::startsWith($string, $needle, $ignoreCase);
    }
}

if(!function_exists('endsWith'))
{
    /**
     * بررسی می کند رشته اصلی با رشته دیگری به پایان میرسد یا نه
     *
     * @param string $string
     * @param string $needle
     * @param bool $ignoreCase
     * @return bool
     */
    function endsWith($string, $needle, $ignoreCase = false)
    {
        return Text::endsWith($string, $needle, $ignoreCase);
    }
}

if(!function_exists('cast'))
{
    /**
     * تغییر نوع آبجکت به کلاسی دیگر
     *
     * @param mixed $object
     * @param string|Type $className
     * @return mixed
     */
    function cast($object, $className)
    {
        if ($className instanceof Type)
            return $className->cast($object);

        if (!class_exists($className))
            throw new InvalidArgumentException("Class '$className' is not exists");

        $type = gettype($object);
        if($type == "array") {
            return unserialize(sprintf(
                'O:%d:"%s"%s',
                strlen($className),
                $className,
                strstr(serialize($object), ':')
            ));
        }

        elseif($type == "object") {
            return unserialize(sprintf(
                'O:%d:"%s"%s',
                strlen($className),
                $className,
                strstr(strstr(serialize($object), '"'), ':')
            ));
        }

        else {
            throw new InvalidArgumentException("Cast '$type' is not supported");
        }
    }
}

if(!function_exists('unserializeTo'))
{
    /**
     * انسریالایز کردن و تبدیل کردن به کلاس مورد نظر
     * 
     * @param string $string
     * @param string|Type $type
     * @return mixed
     */
    function unserializeTo($string, $type)
    {
        return cast(unserialize($string), $type);
    }
}

if(!function_exists('json_decode2'))
{
    /**
     * دیکد کردن جیسون به کلاس دلخواه
     *
     * @param string $json
     * @param string $className
     * @return mixed
     */
    function json_decode2($json, $className) {
        return cast(json_decode($json, true), $className);
    }
}

if(!function_exists('getAbsPath'))
{
    /**
     * گرفتن آدرس مطلق
     *
     * @param string $path
     * @param string $sep
     * @return string
     */
    function getAbsPath($path, $sep = '/')
    {
        $abs = realpath($path);
        if($abs) {
            $path = $abs;
        }
        else {
            if(@$path[0] != '/' && @$path[1] != ':') {
                $path = realpath('.') . "/" . $path;
            }

            if(strpos($path, '..') !== false) {
                $c = 1;
                while($c)
                    $path = preg_replace('/(\/|\\\)[^\/\!\?\|\:\\\]+(\/|\\\)\.\./', '', $path, -1, $c);
            }
            $c = 1;
            while($c)
                $path = preg_replace('/(^|\/|\\\)\.($|\/|\\\)/', '/', $path, -1, $c);

        }

        if($sep == '/')
            $path = str_replace('\\', '/', $path);
        elseif($sep == '\\')
            $path = str_replace('/', '\\', $path);
        else
            $path = str_replace(['/', '\\'], $sep, $path);

        return $path;
    }
}


if(!function_exists('getRelPath'))
{
    /**
     * گرفتن آدرس نسبی
     *
     * @param string $path
     * @param string $base 
     * @return string
     */
    function getRelPath($path, $base = null)
    {
        if($base == null) {
            $base = getAbsPath('.');
        }
        else $base = str_replace('\\', '/', getAbsPath($base));
        $path = getAbsPath($path);
        if(endsWith($base, '/')) $base = substr($base, 0, -1);

        $path = str_split($path);
        $base = str_split($base);
        $max = min(count($path), count($base));
        for($i = 0; $i < $max; $i++) {
            if($path[$i] == $base[$i]) {
                unset($path[$i], $base[$i]);
            }
            else break;
        }

        $path = join('', $path);
        $base = join('', $base);
        if(!$base && strlen($path) && $path[0] == '/') {
            $path = substr($path, 1);
        }
        else {
            $sl = substr_count($base, '/') + 1;
            $back = str_repeat('../', $sl);
            $path = $back . $path;
        }

        return $path;
    }
}

if(!function_exists('trim2'))
{
    /**
     * حذف فاصله های ابتدا و انتها، بصورت خط به خط
     *
     * @param string $string
     * @param string $charlist
     * @return string
     */
    function trim2(string $string, string $charlist = " \t\n\r\0\x0B")
    {
        $lines = explode("\n", $string);
        $lines = array_map(function($line) use(&$charlist) {
            return trim($line, $charlist);
        }, $lines);
        return trim(join("\n", $lines));
    }
}

if(!function_exists('timeFa'))
{
    /**
     * زمان نسبی ای به تابع بدید تا بصورت فارسی فاصله زمانی را به شما بدهد
     * 
     * مثال های ورودی و خروجی:
     * * 5 => 5 ثانیه
     * * 60 => 1 دقیقه
     * * 65 => 1 دقیقه و 5 ثانیه
     *
     * @param int $time_relative
     * @param int $roundBase
     * @return string
     */
    function timeFa($time_relative, $roundBase = -1)
    {
        // Time
        if($roundBase > -1) {
            $time_relative = round($time_relative / $roundBase) * $roundBase;
        }
        
        // Second
        $second = $time_relative % 60;
        $time_relative = ($time_relative - $second) / 60;
        if(!$time_relative) {
            if(!$second) $second = 1;
            return "$second ثانیه";
        }
        if($second) $second = " و $second ثانیه";
        else $second = "";
        
        // Minute
        $minute = $time_relative % 60;
        $time_relative = ($time_relative - $minute) / 60;
        if(!$time_relative) {
            return "$minute دقیقه$second";
        }
        if($minute) $minute = " و $minute دقیقه";
        else $minute = "";
        
        // Hour
        $hour = $time_relative % 24;
        $time_relative = ($time_relative - $hour) / 24;
        if(!$time_relative) {
            return "$hour ساعت$minute$second";
        }
        if($hour) $hour = " و $hour ساعت";
        else $hour = "";
        
        // Day
        $day = $time_relative;
        return "$day روز$hour$minute$second";
    }
}

if(!function_exists('jstrtotime'))
{
    /**
     * تبدیل متن به زمان بصورت شمسی
     * 
     * فرمت های پشتیبانی شده:
     * مثال: `15 مهر 1401 ساعت 12 و 30 دقیقه`
     * مثال: `15 مهر 1401 ساعت 12:00:00`
     * مثال: `12 مهر 1400`
     * مثال: `ساعت 14:00`
     * `1403/02/05 12:00`
     *
     * @param string $string
     * @return int|bool
     */
    function jstrtotime($string, $defaultH = null, $defaultM = null, $defaultS = null, $defaultJm = null, $defaultJd = null, $defaultJy = null)
    {
        $string = tr_num("$string");
        $month = [
            'فروردین' => 1,
            'اردیبهشت' => 2,
            'خرداد' => 3,
            'تیر' => 4,
            'مرداد' => 5,
            'شهریور' => 6,
            'مهر' => 7,
            'آبان' => 8,
            'آذر' => 9,
            'دی' => 10,
            'بهمن' => 11,
            'اسفند' => 12,
        ];
        $mregex = implode("|", array_keys($month));
        $nums = "[" . tr_num("1234567890") . "\d" . "]";
        $hourRegex = "(?:\s*ساعت\s*(?:($nums+)(?:\s*(?:و|\:)\s*($nums+)(?:\s*دقیقه\s*)?(?:\s*(?:و|\:)\s*($nums+)(?:\s*ثانیه\s*)?)?)?)?)";
        $hourRegex2 = str_replace('\s*ساعت', '(?:ساعت)?', $hourRegex);

        // "[DAY] MONTH [YEAR] [HOUR] [MINUTE] [SECOND]"
        if(preg_match("/^\s*($nums+|)\s*($mregex)\s*(?:\s($nums+))?\s*$hourRegex?$/", $string, $match))
        {
            $m = $month[$match[2]];
            return jmktime(
                @$match[4] ?: $defaultH ?? jdate('H', tr_num:'en'),
                @$match[5] ?: $defaultM ?? jdate('i', tr_num:'en'),
                @$match[6] ?: $defaultS ?? jdate('s', tr_num:'en'),
                $m,
                @$match[1] ?: $defaultJd ?? jdate('j', tr_num:'en'),
                @$match[3] ?: $defaultJy ?? jdate('Y', tr_num:'en')
            );
        }
        // [HOUR] [MINUTE] [SECOND]
        elseif(preg_match("/^$hourRegex2$/", $string, $match))
        {
            return jmktime(
                @$match[1] ?: $defaultH ?? jdate('H', tr_num:'en'),
                @$match[2] ?: $defaultM ?? jdate('i', tr_num:'en'),
                @$match[3] ?: $defaultS ?? jdate('s', tr_num:'en'),
                $defaultJd ?? jdate('m', tr_num:'en'),
                $defaultJm ?? jdate('j', tr_num:'en'),
                $defaultJy ?? jdate('Y', tr_num:'en')
            );
        }
        // Y/m/d H:i:s
        elseif(preg_match("/^(?:($nums+)\s*\/($nums+)\s*\/($nums+))?(?:\s+|$)(?:($nums+)(?:\:($nums+)(?:\:($nums+))?)?)?$/", $string, $match))
        {
            return jmktime(
                @$match[4] ?: $defaultH ?? jdate('H', tr_num:'en'),
                @$match[5] ?: $defaultM ?? jdate('i', tr_num:'en'),
                @$match[6] ?: $defaultS ?? jdate('s', tr_num:'en'),
                @$match[2] ?: $defaultJm ?? jdate('m', tr_num:'en'),
                @$match[3] ?: $defaultJd ?? jdate('j', tr_num:'en'),
                @$match[1] ?: $defaultJy ?? jdate('Y', tr_num:'en')
            );
        }

        return false;
    }
}

if(!function_exists('jstrtotimeZero'))
{
    function jstrtotimeZero($string)
    {
        return jstrtotime($string, 0, 0, 0, 0, 0, 0);
    }
}

if(!function_exists('jstrtotimeZeroClock'))
{
    function jstrtotimeZeroClock($string, $defaultJm = null, $defaultJd = null, $defaultJy = null)
    {
        return jstrtotime($string, 0, 0, 0, $defaultJm, $defaultJd, $defaultJy);
    }
}

if(!function_exists('objOrFalse'))
{
    /**
     * اگر ابجکت فالس باشد، فالس را بر میگرداند و در غیر این صورت، یک آبجکت از کلاس شما میسازد با ورودی هایی که داده اید
     *
     * @param string $class
     * @param mixed $object
     * @param mixed ...$args
     * @return mixed
     */
    function objOrFalse($class, $object, ...$args)
    {
        if($object === false)
            return false;

        return new $class($object, ...$args);
    }
}

if(!function_exists('maybeArray'))
{
    /**
     * این تابع، مقدار های کلید هایی که تعریف کردید را بررسی می کند و اگر آرایه باشند، در آرایه نهایی آن را باز می کند
     * 
     * اولویت کلید های تکراری، با آرایه درون آرایه است! این به این معناست که آرایه ورودی شما مقدار های پیشفرض را دارد که در صورت امکان جایگزین می شوند
     *
     * @param array $array
     * @param string ...$ignore
     * @return array
     */
    function maybeArray(array $array, ...$ignore)
    {
        $res = [];

        foreach($array as $key => $value)
        {
            if(!in_array($key, $ignore) && is_array($value))
            {
                foreach($value as $a => $b)
                {
                    $res[$a] = $b;
                }
            }
            elseif(!isset($res[$key]))
            {
                $res[$key] = $value;
            }
        }

        return $res;
    }
}

if(!function_exists('aParse'))
{
    /**
     * پردازش آرایه و انجام دادن عملیات های ابزارها
     * 
     * * `این تابع، تابع کمکی است! تابع اصلی: ATool::parse`
     *
     * @param array $array
     * @param bool $assoc
     * @return array
     */
    function aParse(array $array, $assoc = false)
    {
        return ATool::parse($array, $assoc);
    }
}

if(!function_exists('aEach'))
{
    /**
     * این ایزار برای افزودن مقدار ها به این قسمت آرایست
     *
     * * اگر کالبک خالی باشد، بصورت خام آرایه قرار می گیرد
     * * callback: `function ($value [, $key])`
     * * return value: `$value` or `[$key, $value]` for assoc
     * * yield value: `$value` or `$key => $value` for assoc
     * 
     * می توانید از دو روش ریترن و یلد استفاده کنید
     * 
     * `$nums = aParse([ aEach(range(1,3), function($num) { return $num + 0.5; }) ]); // [1.5, 2.5, 3.5]`
     * `$nums = aParse([ aEach(range(1,3), function($num) { yield $num; yield $num + 0.5; }) ]); // [1, 1.5, 2, 2.5, 3, 3.5]`
     * 
     * * `این تابع، تابع کمکی است! کلاس اصلی: AEach`
     * 
     * @param array $array
     * @param callable $callback
     * @return \Mmb\Tools\ATool\AEach
     */
    function aEach($array, $callback = null)
    {
        return new \Mmb\Tools\ATool\AEach($array, $callback);
    }
}

if(!function_exists('aIter'))
{
    /**
     * این ابزار برای افزودن یک جنراتور به آرایه ست
     * 
     * روش های تعریف:
     * * 1: `function() { yield 1; yield 2; ... }`
     * * 2: `[1, 2, ...]`
     * 
     * * `این تابع، تابع کمکی است! کلاس اصلی: AIter`
     *
     * @param array|Generator|callable|Closure $value
     * @return \Mmb\Tools\ATool\AIter
     */
    function aIter($value)
    {
        return new \Mmb\Tools\ATool\AIter($value);
    }
}

if(!function_exists('aIter2D'))
{
    /**
     * این ابزار برای افزودن یک جنراتور به آرایه ست
     * 
     * روش های تعریف:
     * * 1: `function() { yield 1; yield 2; ... }`
     * * 2: `[1, 2, ...]`
     * 
     * * `این تابع، تابع کمکی است! کلاس اصلی: AIter`
     *
     * @param array|Generator|callable|Closure $value
     * @return \Mmb\Tools\ATool\AIter2D
     */
    function aIter2D($value, $colCount)
    {
        return new \Mmb\Tools\ATool\AIter2D($value, $colCount);
    }
}

if(!function_exists('aIf'))
{
    /**
     * با این ابزار می توانید یک مقدار را در صورت صحیح بودن شرط قرار دهید
     * 
     * * `این تابع، تابع کمکی است! کلاس اصلی: AIf`
     *
     * @param bool|mixed $condision
     * @param mixed $value
     * @return \Mmb\Tools\ATool\AIf
     */
    function aIf($condision, $value)
    {
        return new \Mmb\Tools\ATool\AIf($condision, $value);
    }
}

if(!function_exists('aNone'))
{
    /**
     * این ایزار برای زمانیست که نمی خواهید در این ایندکس مقداری قرار بگیرد
     * 
     * * Example: `aParse([0, 1, $num >= 2 ? 2 : aNone()]);`
     * * `این تابع، تابع کمکی است! کلاس اصلی: ANone`
     * 
     * @return \Mmb\Tools\ATool\ANone
     */
    function aNone()
    {
        return new \Mmb\Tools\ATool\ANone;
    }
}

if(!function_exists('asset'))
{
    /**
     * گرفتن فایل از است
     * 
     * @param string $path
     * @return CURLFile
     */
    function asset($path)
    {
        return Assets::file($path);
    }
}

if(!function_exists('is_debug_mode'))
{
    /**
     * بررسی می کند حالت برنامه روی دیباگ است یا خیر
     * 
     * برای تنظیم حالت دیباگ از کد زیر استفاده کنید:
     * 
     * `\Debug::on();`
     *
     * @return bool
     */
    function is_debug_mode()
    {
        return Debug::isOn();
    }
}

if(!function_exists('responce'))
{
    /**
     * پاسخ به کاربر با متد پیشفرض
     *
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     * @deprecated 0
     */
    function responce($text, array $args = [])
    {
        return Response::response($text, $args);
    }
}

if(!function_exists('responceIt'))
{
    /**
     * پاسخ به کاربر با متد پیشفرض
     * 
     * این متد اجازه تغییر متن را نمی دهد
     *
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     * @deprecated 0
     */
    function responceIt($text, array $args = [])
    {
        return Response::responseIt($text, $args);
    }
}

if(!function_exists('setResponce'))
{
    /**
     * تنظیم متد پاسخگویی
     * 
     * @param string|Closure|callable $args
     * @param string $message
     * @return void
     * @deprecated 0
     */
    function setResponce($callback, $message = null)
    {
        Response::setResponse($callback, $message);
    }
}

if(!function_exists('response'))
{
    /**
     * پاسخ به کاربر با متد پیشفرض
     *
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
    function response($text, array $args = [])
    {
        return Response::response($text, $args);
    }
}

if(!function_exists('responseIt'))
{
    /**
     * پاسخ به کاربر با متد پیشفرض
     * 
     * این متد اجازه تغییر متن را نمی دهد
     *
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
    function responseIt($text, array $args = [])
    {
        return Response::responseIt($text, $args);
    }
}

if(!function_exists('setResponse'))
{
    /**
     * تنظیم متد پاسخگویی
     * 
     * @param string|Closure|callable $args
     * @param string $message
     * @return void
     */
    function setResponse($callback, $message = null)
    {
        Response::setResponse($callback, $message);
    }
}

if(!function_exists('setMessage'))
{
    /**
     * تنظیم پیام پاسخگویی برای پاسخ بعدی
     * 
     * `setMessage("عملیات لغو شد");`
     * 
     * `setMessage([ 'type' => 'photo', 'photo' => $link, 'text' => "Caption" ]);`
     * 
     * @param string|array $message
     * @return void
     */
    function setMessage($message)
    {
        Response::setMessage($message);
    }
}

if(!function_exists('responceMenu'))
{
    /**
     * به کاربر فعلی با منو پاسخ میدهد و منو را نیز در استپ آن ذخیره می کند
     * 
     * از این تابع برای پیام به دیگر کاربران استفاده نکنید
     *
     * `responceMenu($this->menu, "انتخاب کنید:");`
     * 
     * @param MenuBase $menu
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     * @deprecated 0
     */
    function responceMenu(MenuBase $menu, $text = null, array $args = [])
    {
        if(!$text || (is_array($text) && !isset($text['type']) && !isset($text['text'])))
        {
            $args = ($menu->getMessage() ?: []) +  $args;
        }

        if($msg = responce($text, $args + [ 'menu' => $menu ]))
        {
            StepHandler::set($menu->getHandler());
            return $msg;
        }
        return false;
    }
}

if(!function_exists('responceItMenu'))
{
    /**
     * به کاربر فعلی با منو پاسخ میدهد و منو را نیز در استپ آن ذخیره می کند
     * 
     * از این تابع برای پیام به دیگر کاربران استفاده نکنید
     *
     * `responceItMenu($this->menu, "انتخاب کنید:");`
     * 
     * @param MenuBase $menu
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     * @deprecated 0
     */
    function responceItMenu(MenuBase $menu, $text = null, array $args = [])
    {
        if(!$text || (is_array($text) && !isset($text['type']) && !isset($text['text'])))
        {
            $args = ($menu->getMessage() ?: []) +  $args;
        }

        if($msg = responceIt($text, $args + [ 'menu' => $menu ]))
        {
            StepHandler::set($menu->getHandler());
            return $msg;
        }
        return false;
    }
}

if(!function_exists('responseMenu'))
{
    /**
     * به کاربر فعلی با منو پاسخ میدهد و منو را نیز در استپ آن ذخیره می کند
     * 
     * از این تابع برای پیام به دیگر کاربران استفاده نکنید
     *
     * `responseMenu($this->menu, "انتخاب کنید:");`
     * 
     * @param MenuBase $menu
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
    function responseMenu(MenuBase $menu, $text = null, array $args = [])
    {
        if(!$text || (is_array($text) && !isset($text['type']) && !isset($text['text'])))
        {
            $args = ($menu->getMessage() ?: []) +  $args;
        }

        if($msg = response($text, $args + [ 'menu' => $menu ]))
        {
            StepHandler::set($menu->getHandler());
            return $msg;
        }
        return false;
    }
}

if(!function_exists('responseItMenu'))
{
    /**
     * به کاربر فعلی با منو پاسخ میدهد و منو را نیز در استپ آن ذخیره می کند
     * 
     * از این تابع برای پیام به دیگر کاربران استفاده نکنید
     *
     * `responseItMenu($this->menu, "انتخاب کنید:");`
     * 
     * @param MenuBase $menu
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
    function responseItMenu(MenuBase $menu, $text = null, array $args = [])
    {
        if(!$text || (is_array($text) && !isset($text['type']) && !isset($text['text'])))
        {
            $args = ($menu->getMessage() ?: []) +  $args;
        }

        if($msg = responseIt($text, $args + [ 'menu' => $menu ]))
        {
            StepHandler::set($menu->getHandler());
            return $msg;
        }
        return false;
    }
}

if(!function_exists('send'))
{
    /**
     * ارسال پیام به چت فعلی
     *
     * @param array $args
     * @return Msg|false
     */
    function send(array $args = [])
    {
        if($msg = msg())
        {
            return $msg->send($args);
        }
        elseif($chat = chat())
        {
            return $chat->send($args);
        }
        elseif($user = userinfo())
        {
            return $user->send($args);
        }

        return false;
    }
}

if(!function_exists('reply'))
{
    /**
     * پاسخ به پیام فعلی همراه ریپلای
     *
     * @param array $args
     * @return Msg|false
     */
    function reply(array $args = [])
    {
        if($msg = msg())
        {
            return $msg->reply($args);
        }
        elseif($chat = chat())
        {
            return $chat->send($args);
        }
        elseif($user = userinfo())
        {
            return $user->send($args);
        }

        return false;
    }
}

if(!function_exists('replyText'))
{
    /**
     * پاسخ متنی | تابع کمکی
     *
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
    function replyText($text, $args = [])
    {
        if($msg = msg())
        {
            return $msg->replyText($text, $args);
        }
        elseif($chat = chat())
        {
            return $chat->sendMsg($text, $args);
        }
        elseif($user = userinfo())
        {
            return $user->sendMsg($text, $args);
        }

        return false;
    }
}

if(!function_exists('replyMsg'))
{
    /**
     * پاسخ متنی | تابع کمکی
     *
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
    function replyMsg($text, $args = [])
    {
        return replyText($text, $args);
    }
}

if(!function_exists('replyTextMenu'))
{
    /**
     * به کاربر فعلی با منو ریپلای میکند و منو را نیز در استپ آن ذخیره می کند
     * 
     * از این تابع برای پیام به دیگر کاربران استفاده نکنید
     *
     * `replyTextMenu($this->menu, "انتخاب کنید:");`
     * 
     * @param MenuBase $menu
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
    function replyTextMenu(MenuBase $menu, $text = null, $args = [])
    {
        if(!$text || (is_array($text) && !isset($text['type']) && !isset($text['text'])))
        {
            $args = ($menu->getMessage() ?: []) +  $args;
        }

        if($msg = replyText($text, $args + [ 'menu' => $menu ]))
        {
            StepHandler::set($menu->getHandler());
            return $msg;
        }
        return false;
    }
}

if(!function_exists('replyMenu'))
{
    /**
     * به کاربر فعلی با منو ریپلای میکند و منو را نیز در استپ آن ذخیره می کند
     * 
     * از این تابع برای پیام به دیگر کاربران استفاده نکنید
     * 
     * `replyMenu($this->menu, 'photo', [ 'photo' => "https://...", 'text' => "وارد منو شدید" ]);`
     *
     * @param MenuBase $menu
     * @param string|array $type
     * @param array $args
     * @return Msg|false
     */
    function replyMenu(MenuBase $menu, $type = null, $args = [])
    {
        $args = maybeArray([
            'type' => $type,
            'args' => $args,
        ]);
        if(!isset($args['type']) && !isset($args['text']))
        {
            $args = ($menu->getMessage() ?: []) +  $args;
        }

        if($msg = reply($args + [ 'menu' => $menu ]))
        {
            StepHandler::set($menu->getHandler());
            return $msg;
        }
        return false;
    }
}

if(!function_exists('sendTextMenu'))
{
    /**
     * به کاربر فعلی با منو پیام ارسال میکند و منو را نیز در استپ آن ذخیره می کند
     * 
     * از این تابع برای پیام به دیگر کاربران استفاده نکنید
     *
     * `sendTextMenu($this->menu, "انتخاب کنید:");`
     * 
     * @param MenuBase $menu
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
    function sendTextMenu(MenuBase $menu, $text = null, $args = [])
    {
        if(!$text || (is_array($text) && !isset($text['type']) && !isset($text['text'])))
        {
            $args = ($menu->getMessage() ?: []) +  $args;
        }

        if($msg = sendMsg($text, $args + [ 'menu' => $menu ]))
        {
            StepHandler::set($menu->getHandler());
            return $msg;
        }
        return false;
    }
}

if(!function_exists('sendMsgMenu'))
{
    /**
     * به کاربر فعلی با منو پیام ارسال میکند و منو را نیز در استپ آن ذخیره می کند
     * 
     * از این تابع برای پیام به دیگر کاربران استفاده نکنید
     *
     * `sendMsgMenu($this->menu, "انتخاب کنید:");`
     * 
     * @param MenuBase $menu
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
    function sendMsgMenu(MenuBase $menu, $text, $args = [])
    {
        if($msg = sendMsg($text, $args + [ 'menu' => $menu ]))
        {
            StepHandler::set($menu->getHandler());
            return $msg;
        }
        return false;
    }
}

if(!function_exists('sendMenu'))
{
    /**
     * به کاربر فعلی با منو پیام ارسال میکند و منو را نیز در استپ آن ذخیره می کند
     * 
     * از این تابع برای پیام به دیگر کاربران استفاده نکنید
     * 
     * `sendMenu($this->menu, 'photo', [ 'photo' => "https://...", 'text' => "وارد منو شدید" ]);`
     *
     * @param MenuBase $menu
     * @param string|array $type
     * @param array $args
     * @return Msg|false
     */
    function sendMenu(MenuBase $menu, $type = null, $args = [])
    {
        $args = maybeArray([
            'type' => $type,
            'args' => $args,
        ]);
        if(!isset($args['type']) && !isset($args['text']))
        {
            $args = ($menu->getMessage() ?: []) +  $args;
        }

        if($msg = send($args + [ 'menu' => $menu ]))
        {
            StepHandler::set($menu->getHandler());
            return $msg;
        }
        return false;
    }
}

if(!function_exists('sendMsg'))
{
    /**
     * ارسال پیام متنی به این چت | تابع کمکی
     *
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
    function sendMsg($text, $args = [])
    {
        if($chat = chat())
        {
            return $chat->sendMsg($text, $args);
        }
        elseif($user = userinfo())
        {
            return $user->sendMsg($text, $args);
        }

        return false;
    }
}

if(!function_exists('editText'))
{
    /**
     * ویرایش پیام فعلی | تابع کمکی
     * 
     * @param string|array $text
     * @param array $args
     * @return Msg|false
     */
    function editText($text, $args = [])
    {
        return optional(msg())->editText($text, $args) ?: false;
    }
}

if(!function_exists('replyOrEditText'))
{
    /**
     * به پیام کاربر پاسخ می دهد، یا پیام فعلی را ویرایش می کند
     * 
     * اگر کاربر پیام ارسال کرده باشد ریپلای، و اگر روی دکمه کلیک کرده باشد ویرایش می کند
     * 
     * @param string|array $text
     * @param array $args
     * @return Msg|bool
     */
    function replyOrEditText($text, $args = [])
    {
        if(callback())
        {
            return msg()->editText($text, $args);
        }
        
        return replyText($text, $args);
    }
}

if(!function_exists('sendOrEditText'))
{
    /**
     * به کاربر پیام ارسال می کند، یا پیام فعلی را ویرایش می کند
     * 
     * اگر کاربر پیام ارسال کرده باشد پیام میفرستد، و اگر روی دکمه کلیک کرده باشد ویرایش می کند
     * 
     * @param string|array $text
     * @param array $args
     * @return Msg|bool
     */
    function sendOrEditText($text, $args = [])
    {
        if(callback())
        {
            return msg()->editText($text, $args);
        }
        
        return sendMsg($text, $args);
    }
}

if(!function_exists('answer'))
{
    /**
     * پاسخ به کالبک | تابع کمکی
     *
     * @param string $text
     * @param bool $alert
     * @return bool
     */
    function answer($text = null, $alert = false)
    {
        if($callback = callback())
        {
            return $callback->answer($text, $alert);
        }

        return false;
    }
}

if(!function_exists('answerInline'))
{
    /**
     * پاسخ به اینلاین | تابع کمکی
     *
     * @param array $results
     * @param array $args
     * @return bool
     */
    function answerInline($results, $args = [])
    {
        if($inline = inline())
        {
            return $inline->answer($results, $args);
        }

        return false;
    }
}

if(!function_exists('optional'))
{
    /**
     * اگر مقدار نال یا مشابه فالس باشد، کلاس آپشنال را بر میگرداند که هر متغیر یا تابعی از آن را صدا بزنید کار خواهد کرد
     * 
     * `optional(\Msg::$this)->replyText("Send if message is not null");`
     *
     * @template T
     * @param T $value
     * @return T|Optional
     */
    function optional($value) 
    {
        if(!$value)
            return new Optional;
        
        return $value;
    }
}

if(!function_exists('typeOf'))
{
    function typeOf($value)
    {
        $type = gettype($value);

        if($type != 'object')
        {
            return $type;
        }

        return get_class($value);
    }
}

if(!function_exists('config'))
{
    /**
     * کانفیگ
     *
     * @param string|null $name
     * @param mixed|null $value
     * @return \Mmb\Kernel\Config|mixed|null
     */
    function config($name = null, $value = null)
    {
        $config = \Mmb\Kernel\Config::instance();

        if(is_null($name))
        {
            return $config;
        }

        if(is_null($value))
        {
            return $config->get($name);
        }

        $config->set($name, $value);
    }
}

if(!function_exists('app'))
{
    /**
     * ابجکت
     *
     * @template T
     * @param string|class-string<T> $class
     * @return mixed|T
     */
    function app($class)
    {
        return Instance::get($class);
    }
}

if(!function_exists('includeFile'))
{
    function includeFile($file)
    {
        return include($file);
    }
}


if(!function_exists('mmb'))
{
    /**
     * ام ام بی
     *
     * @return Mmb|null
     */
    function mmb()
    {
        return Mmb::$this;
    }
}

if(!function_exists('upd'))
{
    /**
     * آپدیت
     *
     * @return Upd|null
     */
    function upd()
    {
        return Upd::$this;
    }
}

if(!function_exists('msg'))
{
    /**
     * پیام
     *
     * @return Msg|null
     */
    function msg()
    {
        return Msg::$this;
    }
}

if(!function_exists('callback'))
{
    /**
     * کالبک
     *
     * @return Callback|null
     */
    function callback()
    {
        return Callback::$this;
    }
}

if(!function_exists('inline'))
{
    /**
     * اینلاین
     *
     * @return Inline|null
     */
    function inline()
    {
        return Inline::$this;
    }
}

if(!function_exists('chosenInline'))
{
    /**
     * انتخاب اینلاین
     *
     * @return ChosenInline|null
     */
    function chosenInline()
    {
        return ChosenInline::$this;
    }
}

if(!function_exists('chat'))
{
    /**
     * چت فعلی
     *
     * @return Chat|null
     */
    function chat()
    {
        return Chat::$this;
    }
}

if(!function_exists('userinfo'))
{
    /**
     * اطلاعات کاربر فعلی
     *
     * @return UserInfo|null
     */
    function userinfo()
    {
        return UserInfo::$this;
    }
}

if(!function_exists('userid'))
{
    /**
     * گرفتن آیدی کاربر فعلی
     *
     * @return int|string|null
     */
    function userid()
    {
        return userinfo()?->id;
    }
}

if(!function_exists('lang'))
{
    /**
     * گرفتن متن با زبان پیشفرض
     * 
     * @param string $name
     * @param array|mixed $args
     * @param mixed ...$_args
     * @return string
     */
    function lang($name, $args = [], ...$_args)
    {
        return Lang::text($name, $args, ...$_args);
    }
}

if(!function_exists('tryLang'))
{
    /**
     * گرفتن متن با زبان پیشفرض - اگر مقدار وجود نداشت نال برمیگرداند
     * 
     * @param string $name
     * @param array|mixed $args
     * @param mixed ...$_args
     * @return string|null
     */
    function tryLang($name, $args = [], ...$_args)
    {
        try
        {
            return Lang::text($name, $args, ...$_args);
        }
        catch(LangValueNotFound $e)
        {
            return null;
        }
    }
}

if(!function_exists('langFrom'))
{
    /**
     * گرفتن متن با زبان پیشفرض
     * 
     * @param string $name
     * @param string $lang
     * @param array|mixed $args
     * @param mixed ...$_args
     * @return string
     */
    function langFrom($name, $lang, $args = [], ...$_args)
    {
        return Lang::textFromLang($name, $lang, $args, ...$_args);
    }
}

if(!function_exists('tryLangFrom'))
{
    /**
     * گرفتن متن با زبان پیشفرض - اگر مقدار وجود نداشت نال برمیگرداند
     * 
     * @param string $name
     * @param string $lang
     * @param array|mixed $args
     * @param mixed ...$_args
     * @return string|null
     */
    function tryLangFrom($name, $lang, $args = [], ...$_args)
    {
        try
        {
            return Lang::textFromLang($name, $lang, $args, ...$_args);
        }
        catch(LangValueNotFound $e)
        {
            return null;
        }
    }
}


if(!function_exists('env'))
{
    /**
     * گرفتن مقدار انو
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    function env($name, $default = null)
    {
        return Env::get($name, $default);
    }
}

if(!function_exists('guard'))
{
    /**
     * گرفتن کلاس گارد
     *
     * @return Guard
     */
    function guard()
    {
        return app(Guard::class);
    }
}

if(!function_exists('getStep'))
{
    /**
     * گرفتن استپ کاربر فعلی
     *
     * @return StepHandler|null
     */
    function getStep()
    {
        return StepHandler::get();
    }
}

if(!function_exists('setStep'))
{
    /**
     * تنظیم استپ کاربر فعلی
     *
     * @return void
     */
    function setStep($value)
    {
        StepHandler::set($value);
    }
}

if(!function_exists('forelse'))
{
    /**
     * تنظیم استپ کاربر فعلی
     *
     * @param mixed $iterator
     * @param Closure|mixed $callback
     * @param Closure|mixed $else
     * @return void
     */
    function forelse($iterator, $callback, $else)
    {
        $ok = false;
        foreach($iterator as $item)
        {
            $callback($item);
            $ok = true;
        }

        if(!$ok)
        {
            $else();
        }
    }
}

if(!function_exists('error'))
{
    /**
     * خطای کاربر
     * 
     * اگر این خطا هندل نشود، به کاربر نمایش داده می شود
     *
     * @param string $message
     * @throws ExtraErrorMessage
     * @return void
     */
    function error($message)
    {
        throw new ExtraErrorMessage($message);
    }
}

if(!function_exists('arr'))
{
    /**
     * ایجاد کلاس آرایه
     *
     * @template T
     * @param array<T>|Arrayable<T> $array
     * @return Arr<T>
     */
    function arr(Arrayable|array $array)
    {
        return new Arr($array);
    }
}

if(!function_exists('map'))
{
    /**
     * ایجاد کلاس مپ
     *
     * @template T
     * @param array<T>|Arrayable<T> $array
     * @return Map<T>
     */
    function map(Arrayable|array $array)
    {
        return new Map($array);
    }
}



/**
 * این متغیر یک تابع است که مقدار ورودی خود را بر می گرداند
 * 
 * از این متغیر در بین رشته ها استفاده کنید
 * * `"Hello {$f('World')}"`
 * * `"List: {$f(join($array))}"`
 * 
 * @var Closure
 */
global $f;
$f = function(...$values) {
    return join(' ', $values);
};

set_exception_handler(function ($exception)
{
    \Mmb\Core\ErrorHandler::defaultStatic()->error($exception);
});
