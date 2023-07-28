<?php
#auto-name
namespace Mmb\Tools;

use Closure;
use Mmb\Mapping\Arrayable;
use Mmb\Tools\Advanced\AdvancedChanceUserRepeat;
use Mmb\Tools\Advanced\AdvancedFunc;
use Mmb\Tools\Advanced\AdvancedRandom;
use Mmb\Tools\Advanced\AdvancedRandomChance;

class Advanced
{

    /**
     * هربار بصورت تصادفی یکی از مقادیر را انتخاب می کند
     *
     * `Advanced::random([ 'Hey', 'Hi', 'Hello ])`
     * 
     * @param array|Arrayable $array
     * @return AdvancedRandom
     */
    public static function random(array|Arrayable $array)
    {
        return new AdvancedRandom($array);
    }
    
    /**
     * هر بار بصورت تصادفی یکی از مقادیر را انتخاب می کند
     * 
     * هر مقدار مقدار شانس دلخواهی دارد که می توانید آن را تنظیم کنید
     * 
     * `Advanced::randomChance([ ['High', 15], ['Low', 2] ])`
     *
     * @param array|Arrayable $value_chance
     * @return AdvancedRandomChance
     */
    public static function randomChance(array|Arrayable $value_chance)
    {
        return new AdvancedRandomChance($value_chance);
    }
    
    /**
     * بر اساس تعداد بار هایی که کاربر این پیام را دریافت کرده است، شانس مقادیر را تغییر می دهد
     * 
     * بطور خلاصه بعد از هر بار، شانس بیشتری برای آمدن مقدار بعدی وجود خواهد داشت
     *
     * @param string $name اسم یکتا
     * @param array|Arrayable $array
     * @param int $rememberTime حداکثر فاصله گرفتن مقدار، بر اساس ثانیه
     * @param int $focusScale
     * @return AdvancedChanceUserRepeat
     */
    public static function repeatChance($name, array|Arrayable $array, $rememberTime = 1800, $focusScale = 3)
    {
        return new AdvancedChanceUserRepeat($array, $name, $rememberTime, $focusScale);
    }

    /**
     * متدی را تعریف می کنید که مقدار آن را تنها یکبار، آن هم در زمان نیاز بگیرد
     * 
     * `'usersCount' => Advanced::func(function() { return User::count(); })`
     *
     * @param callable|string|array|Closure $callable
     * @return AdvancedFunc
     */
    public static function func($callable)
    {
        return new AdvancedFunc($callable);
    }


    public static function getRealValue(AdvancedValue $value)
    {
        do {
            $value = $value->getValue();
        }
        while($value instanceof AdvancedValue);
        return $value;
    }


    /**
     * این ابزار را در ابزار های سلکتور آرایه استفاده کنید
     * 
     * `$random = [ [ 'hello' => 'Hi!', 'bye' => 'Goodbye!' ], [ 'hello' => 'Salam.', 'bye' => 'Boro baba' ] ];`
     * 
     * `$value = ATool::selectorGet([ 'text' => Advanced::random($random) ], 'text.hello');`
     * 
     * می توانید از این ابزار ها در زبان ها نیز استفاده کنید
     *
     * @return void
     */
    public static function help_with_document()
    {
    }
    
}
