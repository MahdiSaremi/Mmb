<?php

namespace Mmb\Tools; #auto

use Mmb\Exceptions\MmbException;
use Mmb\Tools\ATool\Base;

/**
 * این کلاس برای آرایه های منظم و غیر کلید دار می باشد
 */
class ATool
{

    public const SELECTOR_SET = 1;
    public const SELECTOR_GET = 2;
    public const SELECTOR_GET_LIST = 3;
    public const SELECTOR_UNSET = 4;
    public const SELECTOR_GET_SELECTORS = 5;
    public const SELECTOR_EXISTS = 6;

    /**
     * افزودن مقدار به آرایه
     *
     * @param array $array
     * @param int $index
     * @param mixed $item
     * @return void
     */
    public static function insert(array &$array, $index, $item) {
        array_splice($array, $index, 0, [$item]);
    }

    /**
     * افزودن چند مقدار به آرایه
     *
     * @param array $array
     * @param int $index
     * @param array $items
     * @return void
     */
    public static function insertMulti(array &$array, $index, array $items) {
        array_splice($array, $index, 0, $items);
    }

    /**
     * حذف مقداری از آرایه
     *
     * @param array $array
     * @param int $index
     * @return void
     */
    public static function remove(array &$array, $index) {
        array_splice($array, $index, 1, []);
    }

    /**
     * حذف تمامی مقدار هایی که مطابقت دارند
     *
     * @param array $array
     * @param mixed $needle
     * @param boolean $equals
     * @return void
     */
    public static function remove2(array &$array, $needle, $equals = false) {
        if($equals)
            $array = array_filter($array, function($val) use(&$needle) {
                return $val !== $needle;
            });
        else
            $array = array_filter($array, function($val) use(&$needle) {
                return $val != $needle;
            });
        $array = array_values($array);
    }

    /**
     * جابجا کردن مقداری از ایندکسی به ایندکس دیگر
     *
     * @param array $array
     * @param int $from_index
     * @param int $to_index
     * @return void
     */
    public static function move(array &$array, $from_index, $to_index) {
        $value = @$array[$from_index];
        ATool::remove($array, $from_index);
        if($from_index < $to_index) $to_index--;
        ATool::insert($array, $to_index, $value);
    }

    /**
     * افزودن مقداری به انتهای آرایه
     *
     * @param array $array
     * @param mixed $item
     * @return void
     */
    public static function add(array &$array, $item) {
        $array[] = $item;
    }

    /**
     * افزودن چند مقدار به انتهای آرایه
     *
     * @param array $array
     * @param array $items
     * @return void
     */
    public static function addMulti(array &$array, array $items) {
        array_push($array, ...$items);
    }

    public static function isFirst(array &$array, $index) {
        return $array && $index == 0;
    }

    public static function isLast(array &$array, $index) {
        return $array && count($array) - 1 == $index;
    }

    /**
     * پردازش آرایه و انجام دادن عملیات های ابزارها
     *
     * @param array $array
     * @param boolean $assoc
     * @return array
     */
    public static function parse(array $array, $assoc = false) {
        $r = [];
        if($assoc) {
            foreach($array as $key => $value) {
                if($value instanceof Base) {
                    $value->parse($r, true);
                }
                else {
                    $r[$key] = $value;
                }
            }
        }
        else {
            foreach($array as $value) {
                if($value instanceof Base) {
                    $value->parse($r, false);
                }
                else {
                    $r[] = $value;
                }
            }
        }
        return $r;
    }

    /**
     * انتخاب با سلکتور
     * 
     * * Selector:
     * * `off` = `$array[off]`
     * * `settings.off` = `$array[settings][off]`
     * * `settings.*` = `$array[settings][همه]`
     * * `pers.*.can` = `$array[pers][همه][can]`
     * * `settings.off|text|other` = `$array[settings][off] و $array[settings][text] و $array[settings][other]`
     * * `mylist.+` = `$array[mylist][]`
     * *
     * * Flag:
     * * `ATool::SELECTOR_SET` : انتخاب ها را برابر مقداری که در ورودی آخر کرده اید می کند
     * * `ATool::SELECTOR_GET` : انتخاب را بر میگرداند(تنها یک انتخاب)
     * * `ATool::SELECTOR_GET_LIST` : لیستی از انتخاب ها بر میگرداند
     * * `ATool::SELECTOR_UNSET` : انتخاب ها را از آرایه حذف می کند
     * * `ATool::SELECTOR_GET_SELECTORS` : انتخاب ها را بصورت متغیر های رفرنس بر میگرداند
     * * `ATool::SELECTOR_EXISTS` : بررسی می کند که انتخاب وجود دارد
     *
     * @see https://mmblib.ir/docs/ATool/selector
     * 
     * @param array $array
     * @param string $selector
     * @param int $flag
     * @param mixed $arg
     * @return mixed
     */
    public static function selector(&$array, $selector, $flag, $arg = null) {
        $sel = [&$array];

        // Explode '.'
        $queries = explode('.', $selector);
        $ignoreIsset = $flag == self::SELECTOR_SET;
        $queriesLast = count($queries) - 1;
        $isUnset = $flag == self::SELECTOR_UNSET;

        // Run queries
        foreach($queries as $query_n => $query) {
            $last = $queriesLast == $query_n;

            // Query * (Select all)
            if($query == '*') {
                foreach($sel as $i => $_) {
                    // Select all
                    if(is_array($sel[$i]))
                    foreach($sel[$i] as $key => $__) {
                        if($ignoreIsset || isset($sel[$i][$key])) {
                            if($last && $isUnset) {
                                unset($sel[$i][$key]);
                            }
                            else
                                $sel[] = &$sel[$i][$key];
                        }
                    }
                    // Delete this
                    unset($sel[$i]);
                }
            }

            // Add query
            elseif($query == '+')  {
                foreach($sel as $i => $_) {
                    // Select all
                    $temp = null;
                    $sel[$i][] = &$temp;
                    $sel[$i] = &$temp;
                    unset($temp);
                }
            }

            // Query | (Or selector)
            elseif(strpos($query, '|') !== false) {
                $or = explode('|', $query);
                foreach($sel as $i => $_) {
                    // Select
                    foreach($or as $key) {
                        if($ignoreIsset || isset($sel[$i][$key])) {
                            if($last && $isUnset) {
                                unset($sel[$i][$key]);
                            }
                            else
                                $sel[] = &$sel[$i][$key];
                        }
                    }
                    // Delete this
                    unset($sel[$i]);
                }
            }

            // Normal
            else {
                foreach($sel as $i => $_) {
                    // Select
                    if($ignoreIsset || isset($sel[$i][$query])) {
                        if($last && $isUnset) {
                            unset($sel[$i][$query]);
                        }
                        else
                            $sel[$i] = &$sel[$i][$query];
                    }
                    else {
                        unset($sel[$i]);
                    }
                }
            }
        }

        switch($flag) {
            // Set
            case self::SELECTOR_SET:
                foreach($sel as $i => $_) {
                    $sel[$i] = $arg;
                }
            break;

            // Get
            case self::SELECTOR_GET:
                foreach($sel as $i => $_) {
                    return $sel[$i];
                }
                return $arg;

            // Get list
            case self::SELECTOR_GET_LIST:
                $list = [];
                foreach($sel as $i => $_) {
                    $list[] = $sel[$i];
                }
                return $list;
            
            // Get selectors
            case self::SELECTOR_GET_SELECTORS:
                return array_values($sel);

            // Unset
            case self::SELECTOR_GET:
                foreach($sel as $i => $_) {
                    unset($sel[$i]);
                }
            break;

            // Unset
            case self::SELECTOR_EXISTS:
                return $sel ? true : false;
        }
    }

    /**
     * انتخاب با سلکتور و تنظیم مقدار های آن
     * 
     * * `selectorSet($array, 'admins.*.pers.send|delete', true);` = `$array[admins][همه][pers][send و delete] = true;`
     *
     * @param array $array
     * @param string $selector
     * @param mixed $value
     * @return void
     */
    public static function selectorSet(&$array, $selector, $value) {
        self::selector($array, $selector, self::SELECTOR_SET, $value);
    }

    /**
     * انتخاب با سلکتور و گرفتن مقدار(تنها یک مقدار)
     * 
     * * `selectorGet($array, 'admins.*.pers.send');` = `$array[admins][همه][pers][send];`
     *
     * @param array $array
     * @param string $selector
     * @param mixed $default
     * @return mixed
     */
    public static function selectorGet(&$array, $selector, $default = null) {
        return self::selector($array, $selector, self::SELECTOR_GET, $default);
    }

    /**
     * انتخاب با سلکتور و گرفتن تمام مقدار ها
     * 
     * * `selectorGetList($array, 'admins.*');` = `$array[admins][همه];`
     *
     * @param array $array
     * @param string $selector
     * @return array
     */
    public static function selectorGetList(&$array, $selector) {
        return self::selector($array, $selector, self::SELECTOR_GET_LIST);
    }

    /**
     * انتخاب با سلکتور و گرفتن تمام مقدار ها
     * 
     * * `selectorGetSelectors($array, 'admins.*');` = `&$array[admins][همه];`
     *
     * @param array $array
     * @param string $selector
     * @return array
     */
    public static function selectorGetSelectors(&$array, $selector) {
        return self::selector($array, $selector, self::SELECTOR_GET_SELECTORS);
    }

    /**
     * انتخاب با سلکتور و حذف تعریف های آنها
     * 
     * * `selectorUnset($array, 'admins.*');` = `$array[admins][همه];`
     *
     * @param array $array
     * @param string $selector
     * @return void
     */
    public static function selectorUnset(&$array, $selector) {
        self::selector($array, $selector, self::SELECTOR_UNSET);
    }

    /**
     * انتخاب با سلکتور و بررسی وجود
     * 
     * * `selectorUnset($array, 'admins.*');` = `$array[admins][همه];`
     *
     * @param array $array
     * @param string $selector
     * @return bool
     */
    public static function selectorExists(&$array, $selector) {
        return self::selector($array, $selector, self::SELECTOR_EXISTS);
    }

    /**
     * این تابع بررسی می کند که سلکتور شما، شامل هیچ کاراکتر دستورات سلکتنور نباشد
     *
     * @param string $selector_name
     * @return bool
     */
    public static function selectorValidName($selector_name) {
        foreach(['.', '*', '|'] as $char) {
            if(strpos($selector_name, $char) !== false)
                return false;
        }
        return true;
    }


    // Filter array
    public static function filterArray($array, $keys, $vals=null, $delEmpties1 = false)
    {
        if($keys == null)
            $a = "n";
        elseif(gettype($keys) == "array")
            $a = "a";
        else
            $a = "c";
        if($vals == null)
            $b = "n";
        elseif(gettype($vals) == "array")
            $b = "a";
        else
            $b = "c";
        $r = [];
        foreach($array as $key => $val){
            if($delEmpties1 && $val == null) continue;
            if($a == "a"){
                if(isset($keys[$key]))
                    $key = $keys[$key];
                elseif(($_ = strtolower($key)) && isset($keys[$_]))
                    $key = $keys[$_];
                else
                    return false;
            }elseif($a == "c"){
                $key = $keys($key);
                if($key === false)
                    return false;
            }
            if($b == "a"){
                if(isset($vals[$val]))
                    $val = $vals[$val];
            }elseif($b == "c"){
                $val = $vals($key, $val);
            }
            $r[$key] = $val;
        }
        return $r;
    }

    public static function filterArray2D($array, $keys, $vals=null, $delEmpties2 = false, $delEmpties1 = false)
    {
        $new = [];
        foreach($array as $i => $val){
            if($delEmpties2 && $val == null) continue;
            if(($a = self::filterArray($val, $keys, $vals, $delEmpties1)) === false)
                return false;
            if($delEmpties2 && !$a) continue;
            $new[] = $a;
        }
        return $new;
    }
    
    public static function filterArray3D($array, $keys, $vals=null, $delEmpties3 = false, $delEmpties2 = false, $delEmpties1 = false)
    {
        $new = [];
        foreach($array as $i => $val){
            if($delEmpties3 && $val == null) continue;
            if(($a = self::filterArray2D($val, $keys, $vals, $delEmpties2, $delEmpties1)) === false)
                return false;
            if($delEmpties3 && !$a) continue;
            $new[] = $a;
        }
        return $new;
    }
    

    public static function make2D(array $array, $colCount)
    {
        if ($colCount <= 0)
            throw new \InvalidArgumentException("AToll::make2D() : \$colCount value must be bigger than zero, given $rowCount");

        $res = [];
        $count = count($array);

        for($i = 0; $i < $count; )
        {
            $row = [];
            for($j = 0; $j < $colCount && $i < $count; $j++, $i++)
            {
                $row[] = $array[$i];
            }
            $res[] = $row;
        }

        return $res;
    }

}
