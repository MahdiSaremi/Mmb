<?php

// Copyright (C): t.me/MMBlib

use Mmb\Assets\Assets;
use Mmb\Debug\Debug;
use Mmb\Exceptions\MmbException;
use Mmb\Kernel\Instance;
use Mmb\Kernel\Kernel;
use Mmb\Lang\Lang;
use Mmb\Mmb;
use Mmb\Tools\ATool;
use Mmb\Tools\InlineResult;
use Mmb\Tools\Keys;
use Mmb\Tools\Optional;
use Mmb\Update\Callback\Callback;
use Mmb\Update\Chat\Per;
use Mmb\Update\Inline\ChosenInline;
use Mmb\Update\Inline\Inline;
use Mmb\Update\Message\Msg;
use Mmb\Update\Upd;


Kernel::$runTime = microtime(true);

/**
 * ساخت کیبورد
 *
 * @param array $key دکمه ها
 * @param bool|null $inline اینلاین بودن
 * @param boolean $resize ریسایز خودکار
 * @param boolean $encode انکد کردن نتیجه
 * @param boolean $once کیبورد یکباره
 * @param boolean $selective سلکتیو
 * @return string|array
 */
function mkey($key, $inline=null, $resize=true, $encode=true, $once=false, $selective=false)
{
    return Keys::makeKey($key, $inline, $resize, $encode, $once, $selective);
}

function mPers($ar)
{
    return Per::makePers($ar);
}

function mInlineRes($results)
{
    return InlineResult::makeResult($results);
}

function mInlineRes_A($data)
{
    return InlineResult::makeSingle($data);
}

function filterArray($array, $keys, $vals=null, $delEmpties1 = false)
{
    return ATool::filterArray($array, $keys, $vals, $delEmpties1);
}

function filterArray2D($array, $keys, $vals=null, $delEmpties2 = false, $delEmpties1 = false)
{
    return ATool::filterArray2D($array, $keys, $vals, $delEmpties2, $delEmpties1);
}

function filterArray3D($array, $keys, $vals=null, $delEmpties3 = false, $delEmpties2 = false, $delEmpties1 = false)
{
    return ATool::filterArray3D($array, $keys, $vals, $delEmpties3, $delEmpties2, $delEmpties1);
}

function mmb_log($text)
{
    if(Mmb::$LOG)
        error_log($text, 0);
        //file_put_contents("mmb_log", "\n[" . date("Y/m/d H:i:s") . "] $text", FILE_APPEND);
    return $text;
}

function mmb_error_throw($des, $must_throw_error = false)
{
    /*if(Mmb::$LOG)
        mmb_log($des);*/
    if($must_throw_error || Mmb::$HARD_ERROR)
        throw new MmbException($des);
}

/**
 * سریالایز کردن تابع
 *
 * @param Closure $closure
 * @return string
 */
function serializeClosure(Closure $closure){
    // require_once __DIR__ . '/Opis.php';
    return serialize(new \Opis\Closure\SerializableClosure($closure));
}

/**
 * انسریالایز کردن تابع
 *
 * @param string $closure
 * @return \Opis\Closure\SerializableClosure
 */
function unserializeClosure(string $closure){
    // require_once __DIR__ . '/Opis.php';
    return unserialize($closure);
}

/**
 * با صرف نظر کردن از بزرگی و کوچکی حروف، دو رشته را با هم مقایسه می کند
 *
 * @param string $value1
 * @param string ...$values
 * @return bool
 */
function eqi($value1, $value2){
    return strtolower($value1) == strtolower($value2);
}

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
function clamp($number, $min, $max) {
    if($number > $max) return $max;
    if($number < $min) return $min;
    return $number;
}

/**
 * حذف پوشه و محتویات آن
 *
 * @param string $dirPath
 * @return bool
 */
function delDir($dirPath) {
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


/**
 * انکد کردن کاراکتر ها برای مد اچ تی ام ال تلگرام
 *
 * @param string $text
 * @return string
 */
function htmlEncode($text){
    return str_replace([
        '&', '<', '>',
    ], [
        "&amp;", "&lt;", "&gt;",
    ], $text);
}

/**
 * انکد کردن کاراکتر ها برای مد مارک داون تلگرام
 *
 * @param string $text
 * @return string
 */
function markdownEncode($text){
    return str_replace([
        "\\", '_', '*', '`', '['
    ], [
        "\\\\", "\\_", "\\*", "\\`", "\\[",
    ], $text);
}

/**
 * انکد کردن کاراکتر ها برای مد مارک داون2 تلگرام
 *
 * @param string $text
 * @return string
 */
function markdown2Encode($text){
    return preg_replace('/[\\\\_\*\[\]\(\)~`>\#\+\-=\|\{\}\.\!]/', '\\\\$0', $text);
}

/**
 * بررسی می کند رشته اصلی با رشته دیگری شروع می شود یا نه
 *
 * @param string $string
 * @param string $needle
 * @param boolean $ignoreCase
 * @return bool
 */
function startsWith($string, $needle, $ignoreCase = false) {
    $s = @substr($string, 0, strlen($needle));
    if($ignoreCase)
        return eqi($s, $needle);
    else
        return $s == $needle;
}

/**
 * بررسی می کند رشته اصلی با رشته دیگری به پایان میرسد یا نه
 *
 * @param string $string
 * @param string $needle
 * @param boolean $ignoreCase
 * @return bool
 */
function endsWith($string, $needle, $ignoreCase = false) {
    $s = @substr($string, -strlen($needle));
    if($ignoreCase)
        return eqi($s, $needle);
    else
        return $s == $needle;
}

/**
 * تغییر نوع آبجکت به کلاسی دیگر
 *
 * @param mixed $object
 * @param string $className
 * @return mixed
 */
function cast($object, $className) {
    if(!class_exists($className))
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

/**
 * گرفتن آدرس مطلق
 *
 * @param string $path
 * @param string $sep
 * @return string
 */
function getAbsPath($path, $sep = '/') {
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


/**
 * گرفتن آدرس نسبی
 *
 * @param string $path
 * @param string $base 
 * @return string
 */
function getRelPath($path, $base = null) {
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
    if(!$base && $path[0] == '/') {
        $path = substr($path, 1);
    }
    else {
        $sl = substr_count($base, '/') + 1;
        $back = str_repeat('../', $sl);
        $path = $back . $path;
    }

    return $path;
}

/**
 * حذف فاصله های ابتدا و انتها، بصورت خط به خط
 *
 * @param string $string
 * @param string $charlist
 * @return string
 */
function trim2(string $string, string $charlist = " \t\n\r\0\x0B") {
    $lines = explode("\n", $string);
    $lines = array_map(function($line) use(&$charlist) {
        return trim($line, $charlist);
    }, $lines);
    return trim(join("\n", $lines));
}

/**
 * زمان نسبی ای به تابع بدید تا بصورت فارسی فاصله زمانی را به شما بدهد
 * 
 * مثال های ورودی و خروجی:
 * * 5 => 5 ثانیه
 * * 60 => 1 دقیقه
 * * 65 => 1 دقیقه و 5 ثانیه
 *
 * @param integer $time_relative
 * @param integer $roundBase
 * @return string
 */
function timeFa($time_relative, $roundBase = -1) {
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

/**
 * اگر ابجکت فالس باشد، فالس را بر میگرداند و در غیر این صورت، یک آبجکت از کلاس شما میسازد با ورودی هایی که داده اید
 *
 * @param string $class
 * @param mixed $object
 * @param mixed ...$args
 * @return mixed
 */
function objOrFalse($class, $object, ...$args) {

    if($object === false)
        return false;

    return new $class($object, ...$args);

}

/**
 * این تابع، مقدار های کلید هایی که تعریف کردید را بررسی می کند و اگر آرایه باشند، در آرایه نهایی آن را باز می کند
 * 
 * اولویت کلید های تکراری، با آرایه درون آرایه است! این به این معناست که آرایه ورودی شما مقدار های پیشفرض را دارد که در صورت امکان جایگزین می شوند
 *
 * @param array $array
 * @param string ...$ignore
 * @return array
 */
function maybeArray(array $array, ...$ignore) {

    $res = [];

    foreach($array as $key => $value) {

        if(!in_array($key, $ignore) && is_array($value)) {

            foreach($value as $a => $b) {
                $res[$a] = $b;
            }
            
        }
        elseif(!isset($res[$key])) {

            $res[$key] = $value;

        }

    }

    return $res;

}

/**
 * پردازش آرایه و انجام دادن عملیات های ابزارها
 * 
 * * `این تابع، تابع کمکی است! تابع اصلی: ATool::parse`
 *
 * @param array $array
 * @param boolean $assoc
 * @return array
 */
function aParse(array $array, $assoc = false) {
    return ATool::parse($array, $assoc);
}

/**
 * این ایزار برای افزودن مقدار ها به این قسمت آرایست
 *
 * * اگر کالبک خالی باشد، بصورت خام آرایه قرار می گیرد
 * * callback: `function ($value [, $key])`
 * * return value: `$value` or `[$key, $value]`
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

/**
 * این ابزار برای افزودن یک جنراتور به آرایه ست
 * 
 * روش های تعریف:
 * * 1: `function() { yield 1; yield 2; ... }`
 * * 2: `[1, 2, ...]`
 * 
 * * `این تابع، تابع کمکی است! کلاس اصلی: AIter`
 *
 * @param array|Generator|Callable|Closure $function
 * @return \Mmb\Tools\ATool\AIter
 */
function aIter($value)
{
    return new \Mmb\Tools\ATool\AIter($value);
}

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

/**
 * این ایزار برای زمانیست که نمی خواهید در این ایندکس مقداری قرار بگیرد
 * 
 * * Example: `aParse([0, 1, $num >= 2 ? 2 : aNone()]);`
 * * `این تابع، تابع کمکی است! کلاس اصلی: ANone`
 * 
 * @return \Mmb\Tools\ATool\ANone
 */
function aNone() {
    return new \Mmb\Tools\ATool\ANone;
}

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

/**
 * بررسی می کند حالت برنامه روی دیباگ است یا خیر
 * 
 * برای تنظیم حالت دیباگ از کد زیر استفاده کنید:
 * 
 * `\Debug::on();`
 *
 * @return boolean
 */
function is_debug_mode() {
    return Debug::isOn();
}

/**
 * پاسخ متنی | تابع کمکی
 *
 * @param string|array $text
 * @param array $args
 * @return Msg|false
 */
function replyText($text, $args = []){
    return Msg::$this->replyText($text, $args);
}

/**
 * ارسال پیام متنی به این چت | تابع کمکی
 *
 * @param string|array $text
 * @param array $args
 * @return Msg|false
 */
function sendMsg($text, $args = []){
    return Msg::$this->sendMsg($text, $args);
}

/**
 * پاسخ به کالبک | تابع کمکی
 *
 * @param string $text
 * @param bool $alert
 * @return bool
 */
function answer($text = null, $alert = false){
    return Callback::$this->answer($text, $alert);
}

/**
 * پاسخ به اینلاین | تابع کمکی
 *
 * @param array $results
 * @param array $args
 * @return bool
 */
function answerInline($results, $args = []){
    return Inline::$this->answer($results, $args);
}

/**
 * اگر مقدار نال یا مشابه فالس باشد، کلاس آپشنال را بر میگرداند که هر متغیر یا تابعی از آن را صدا بزنید کار خواهد کرد
 * 
 * @example `optional(\Msg::$this)->replyText("Send if message is not null");`
 *
 * @param mixed $value
 * @return mixed|Optional
 */
function optional($value) {
    
    if(!$value)
        return new Optional;
    
    return $value;

}

function typeOf($value)
{

    $type = gettype($value);

    if($type != 'object')
    {
        return $type;
    }

    return get_class($value);

}

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

/**
 * ابجکت
 *
 * @return mixed
 */
function app($class)
{
    return Instance::get($class);
}

function includeFile($file)
{
    return include($file);
}


/**
 * ام ام بی
 *
 * @return Mmb|null
 */
function mmb()
{
    return Mmb::$this;
}

/**
 * آپدیت
 *
 * @return Upd|null
 */
function upd()
{
    return Upd::$this;
}

/**
 * پیام
 *
 * @return Msg|null
 */
function msg()
{
    return Msg::$this;
}

/**
 * کالبک
 *
 * @return Callback|null
 */
function callback()
{
    return Callback::$this;
}

/**
 * اینلاین
 *
 * @return Inline|null
 */
function inline()
{
    return Inline::$this;
}

/**
 * انتخاب اینلاین
 *
 * @return ChosenInline|null
 */
function chosenInline()
{
    return ChosenInline::$this;
}

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

set_exception_handler(function ($exception) {

    \Mmb\Handlers\ErrorHandler::defaultStatic()->error($exception);

});
