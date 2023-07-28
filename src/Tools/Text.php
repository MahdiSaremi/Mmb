<?php
#auto-name
namespace Mmb\Tools;

class Text
{

    /**
     * موقعیت متنی در متن دیگر
     * 
     * در صورت پیدا نکردن -1 را بر می گرداند
     *
     * @param string $string
     * @param string $search
     * @param int|null $offset
     * @return int
     */
    public static function indexOf($string, $search, $offset = null)
    {
        $index = strpos($string, $search, $offset);
        return $index === false ? -1 : $index;
    }

    /**
     * موقعیت متنی در متن دیگر از انتها
     * 
     * در صورت پیدا نکردن -1 را بر می گرداند
     *
     * @param string $string
     * @param string $search
     * @param int|null $offset
     * @return int
     */
    public static function lastIndexOf($string, $search, $offset = null)
    {
        $index = strrpos($string, $search, $offset === null ? 0 : $offset - strlen($string));
        return $index === false ? -1 : $index;
    }

    /**
     * دنبال متن مورد نظر می گردد و بعد از آن نقطه را بر می گرداند
     * 
     * در صورت پیدا نکردن، نال بر می گرداند
     *
     * @param string $string
     * @param string $search
     * @return string|null
     */
    public static function after($string, $search, $offset = null)
    {
        $index = static::indexOf($string, $search, $offset);
        if($index === -1)
            return null;

        return substr($string, $index + strlen($search));
    }
    
    /**
     * دنبال متن مورد نظر می گردد و قبل از آن نقطه را بر می گرداند
     * 
     * در صورت پیدا نکردن، نال بر می گرداند
     *
     * @param string $string
     * @param string $search
     * @return string|null
     */
    public static function before($string, $search, $offset = null)
    {
        $index = static::indexOf($string, $search, $offset);
        if($index === -1)
            return null;

        return substr($string, 0, $index);
    }
    
    /**
     * دنبال متن مورد نظر می گردد و بعد از آن نقطه را بر می گرداند
     * 
     * در صورت پیدا نکردن، نال بر می گرداند
     *
     * @param string $string
     * @param string $search
     * @return string|null
     */
    public static function afterLast($string, $search, $offset = null)
    {
        $index = static::lastIndexOf($string, $search, $offset);
        if($index === -1)
            return null;

        return substr($string, $index + strlen($search));
    }
    
    /**
     * دنبال متن مورد نظر می گردد و قبل از آن نقطه را بر می گرداند
     * 
     * در صورت پیدا نکردن، نال بر می گرداند
     *
     * @param string $string
     * @param string $search
     * @return string|null
     */
    public static function beforeLast($string, $search, $offset = null)
    {
        $index = static::lastIndexOf($string, $search, $offset);
        if($index === -1)
            return null;

        return substr($string, 0, $index);
    }
    
    /**
     * بخشی از متن را برش می زند
     *
     * @param string $string
     * @param int $offset
     * @param int|null $length
     * @return string
     */
    public static function sub($string, $offset, $length = null)
    {
        if($length === null)
            return substr($string, $offset);
        else
            return substr($string, $offset, $length);
    }

    /**
     * بخشی از متن را برش می زند
     *
     * @param string $string
     * @param int $from
     * @param int $to
     * @return string
     */
    public static function between($string, $from, $to)
    {
        return substr($string, $from, $to - $from);
    }

    /**
     * تبدیل متن به حالت شتری
     *
     * @param string $string
     * @return string
     */
    public static function camel($string)
    {
        $string = preg_replace_callback('/([\s\r\b\n]+)(\w)/',
            function($val)
            {
                return strtoupper($val[2]);
            }
        , $string);
        return strtolower(@$string[0]) . @substr($string, 1);
    }

    /**
     * تبدیل متن به حالت پاسکال
     *
     * @param string $string
     * @return string
     */
    public static function pascal($string)
    {
        $string = preg_replace_callback('/([\s\r\b\n]+)(\w)/',
            function($val)
            {
                return strtoupper($val[2]);
            }
        , $string);
        return strtoupper(@$string[0]) . @substr($string, 1);
    }

    /**
     * تبدیل متن به حالت مار
     *
     * @param string $string
     * @return string
     */
    public static function snake($string)
    {
        $string = preg_replace_callback('/([\s\r\b\n]+)(\w)/',
            function($val)
            {
                return '_' . strtolower($val[2]);
            }
        , $string);
        $string = preg_replace_callback('/[A-Z]/',
            function($val)
            {
                return '_' . strtolower($val[0]);
            }
        , $string);
        return trim($string, '_');
    }

    /**
     * تبدیل متن به حالت کباب
     *
     * @param string $string
     * @return string
     */
    public static function kebab($string)
    {
        $string = preg_replace_callback('/([\s\r\b\n]+)(\w)/',
            function($val)
            {
                return '-' . strtolower($val[2]);
            }
        , $string);
        $string = preg_replace_callback('/[A-Z]/',
            function($val)
            {
                return '-' . strtolower($val[0]);
            }
        , $string);
        return trim($string, '-');
    }

    /**
     * بزرگ کردن حروف متن
     *
     * @param string $string
     * @return string
     */
    public static function upper($string)
    {
        return strtoupper($string);
    }

    /**
     * کوچک کردن حروف متن
     *
     * @param string $string
     * @return string
     */
    public static function lower($string)
    {
        return strtolower($string);
    }

    /**
     * بررسی وجود متن در متن دیگر
     *
     * @param string $string
     * @param string|string[] $search
     * @return boolean
     */
    public static function contains($string, $search)
    {
        if(is_array($search))
        {
            foreach($search as $needle)
                if(str_contains($string, $needle))
                    return true;
            return false;
        }

        return str_contains($string, $search);
    }

    /**
     * بررسی وجود همه آرایه در متن دیگر
     *
     * @param string $string
     * @param array $search
     * @return boolean
     */
    public static function containsAll($string, $search)
    {
        foreach($search as $needle)
            if(!str_contains($string, $needle))
                return false;
        return true;
    }

    /**
     * متن را محدود به یک طول می کند و در صورت بزرگ بودن، آن را می برد
     *
     * @param string $string
     * @param integer $limit
     * @param string $end
     * @return string
     */
    public static function limit($string, $limit = 60, $end = '...')
    {
        if(mb_strlen($string) > $limit)
        {
            return mb_substr($string, 0, $limit) . $end;
        }
        
        return $string;
    }

    /**
     * متن را محدود به یک طول می کند و در صورت بزرگ بودن، آن را می برد
     * 
     * این تابع، اولین خط را حساب می کند
     *
     * @param string $string
     * @param integer $limit
     * @param string $end
     * @return string
     */
    public static function limitLine($string, $limit = 60, $end = '...')
    {
        if(str_contains($string, "\n"))
        {
            return mb_substr(static::before($string, "\n"), 0, $limit) . $end;
        }

        if(mb_strlen($string) > $limit)
        {
            return mb_substr($string, 0, $limit) . $end;
        }
        
        return $string;
    }

    /**
     * انکد کردن کاراکتر ها برای مد اچ تی ام ال تلگرام
     *
     * @param string $string
     * @return string
     */
    public static function htmlEncode($string)
    {
        return str_replace([
            '&', '<', '>',
        ], [
            "&amp;", "&lt;", "&gt;",
        ], $string);
    }

    /**
     * انکد کردن کاراکتر ها برای مد مارک داون تلگرام
     *
     * @param string $string
     * @return string
     */
    public static function markdownEncode($string)
    {
        return str_replace([
            "\\", '_', '*', '`', '['
        ], [
            "\\\\", "\\_", "\\*", "\\`", "\\[",
        ], $string);
    }

    /**
     * انکد کردن کاراکتر ها برای مد مارک داون 2 تلگرام
     *
     * @param string $string
     * @return string
     */
    public static function markdown2Encode($string)
    {
        return preg_replace('/[\\\\_\*\[\]\(\)~`>\#\+\-=\|\{\}\.\!]/', '\\\\$0', $string);
    }

    /**
     * بررسی می کند رشته اصلی با رشته دیگری شروع می شود یا نه
     *
     * @param string $string
     * @param string $needle
     * @param bool $ignoreCase
     * @return bool
     */
    public static function startsWith($string, $needle, $ignoreCase = false)
    {
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
     * @param bool $ignoreCase
     * @return bool
     */
    public static function endsWith($string, $needle, $ignoreCase = false)
    {
        $s = @substr($string, -strlen($needle));
        if($ignoreCase)
            return eqi($s, $needle);
        else
            return $s == $needle;
    }

    /**
     * بررسی می کند رشته ها با هم برابرند
     *
     * @param string|string[] ...$strings
     * @return bool
     */
    public static function equals(...$strings)
    {
        if(!$strings)
            return true;

        if(count($strings) == 1 && is_array($strings[0]))
            $strings = $strings[0];

        $target = $strings[0];
        for($i = 1; $i < count($strings); $i++)
            if($strings[$i] != $target)
                return false;
        return true;
    }

    /**
     * بررسی می کند رشته ها بصورت غیرحساس به حروف با هم برابرند
     *
     * @param string|string[] ...$strings
     * @return bool
     */
    public static function equalsIgnoreCase(...$strings)
    {
        if(!$strings)
            return true;

        if(count($strings) == 1 && is_array($strings[0]))
            $strings = $strings[0];

        $target = strtolower($strings[0]);
        for($i = 1; $i < count($strings); $i++)
            if(strtolower($strings[$i]) != $target)
                return false;
        return true;
    }

    /**
     * یک رشته تصادفی از تمامی کاراکتر ها می سازد
     *
     * @param integer $length
     * @return string
     */
    public static function randomString($length = 16)
    {
        $string = "";
        for($i = 0; $i < $length; $i++)
        {
            $string .= chr(rand(0, 255));
        }
        return $string;
    }

    /**
     * یک رشته تصادفی از آرایه ای که می دهید می سازد
     *
     * @param array $characters
     * @param integer $length
     * @return string
     */
    public static function randomFrom(array $characters, $length = 16)
    {
        $string = "";
        for($i = 0; $i < $length; $i++)
        {
            $string .= $characters[array_rand($characters)];
        }
        return $string;
    }

    /**
     * یک رشته تصادفی از حروف و اعداد و _ می دهد
     *
     * @param integer $length
     * @return string
     */
    public static function random($length = 16)
    {
        static $valid = null;
        if(!$valid) $valid = str_split("qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM0123456789_");
        return static::randomFrom($valid, $length);
    }

    /**
     * یک رشته تصادفی از 0 و 1 می سازد
     *
     * @param integer $length
     * @return string
     */
    public static function randomBase2($length = 16)
    {
        static $valid = null;
        if(!$valid) $valid = str_split("01");
        return static::randomFrom($valid, $length);
    }

    /**
     * یک رشته تصادفی از حروف بیس16 می سازد
     *
     * @param integer $length
     * @return string
     */
    public static function randomBase16($length = 16)
    {
        static $valid = null;
        if(!$valid) $valid = str_split("0123456789ABCDEF");
        return static::randomFrom($valid, $length);
    }

    /**
     * یک رشته تصادفی از حروف بیس64 می سازد
     *
     * @param integer $length
     * @return string
     */
    public static function randomBase64($length = 16)
    {
        static $valid = null;
        if(!$valid) $valid = str_split("0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM+/");
        return static::randomFrom($valid, $length);
    }

    /**
     * یک رشته را به تعداد معلوم تکرار می کند
     *
     * @param string $string
     * @param int $times
     * @return string
     */
    public static function repeat($string, $times)
    {
        return str_repeat($string, $times);
    }

    /**
     * حداقل طول رشته را فیلتر میکند و اگر طول رشته کمتر از آن بود، کاراکتر شما را در ابتدای رشته اضافه می کند
     *
     * @param string $string
     * @param int $minLength
     * @param string $fill
     * @return string
     */
    public static function minStart($string, $minLength, $fill)
    {
        $length = strlen($string);
        if($length < $minLength)
        {
            return str_repeat($fill, $minLength - $length) . $string;
        }

        return $string;
    }

    /**
     * حداقل طول رشته را فیلتر میکند و اگر طول رشته کمتر از آن بود، کاراکتر شما را در انتهای رشته اضافه می کند
     *
     * @param string $string
     * @param int $minLength
     * @param string $fill
     * @return string
     */
    public static function minEnd($string, $minLength, $fill)
    {
        $length = strlen($string);
        if($length < $minLength)
        {
            return $string . str_repeat($fill, $minLength - $length);
        }

        return $string;
    }
    

    /**
     * حداقل طول رشته بر اساس یونیکد را فیلتر میکند و اگر طول رشته کمتر از آن بود، کاراکتر شما را در ابتدای رشته اضافه می کند
     *
     * @param string $string
     * @param int $minLength
     * @param string $fill
     * @return string
     */
    public static function minUnicodeStart($string, $minLength, $fill)
    {
        $length = mb_strlen($string);
        if($length < $minLength)
        {
            return str_repeat($fill, $minLength - $length) . $string;
        }

        return $string;
    }

    /**
     * حداقل طول رشته بر اساس یونیکد را فیلتر میکند و اگر طول رشته کمتر از آن بود، کاراکتر شما را در انتهای رشته اضافه می کند
     *
     * @param string $string
     * @param int $minLength
     * @param string $fill
     * @return string
     */
    public static function minUnicodeEnd($string, $minLength, $fill)
    {
        $length = mb_strlen($string);
        if($length < $minLength)
        {
            return $string . str_repeat($fill, $minLength - $length);
        }

        return $string;
    }

}
