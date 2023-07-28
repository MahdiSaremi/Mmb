<?php

namespace Mmb\Db; #auto

use Mmb\Exceptions\MmbException;
use Mmb\Exceptions\TypeException;

abstract class QueryCompiler {

    /**
     * جدول موردنظر
     *
     * @var string
     */
    public $table;

    /**
     * جوین ها
     *
     * @var array
     */
    public $joins;

    /**
     * شرط ها
     *
     * @var array
     */
    public $where;

    /**
     * شرط ها
     *
     * @var array
     */
    public $having;

    /**
     * محدودیت تعداد
     *
     * @var int|false
     */
    public $limit;

    /**
     * محل شروع انتخاب
     *
     * @var int|false
     */
    public $offset;

    /**
     * مرتب سازی بر اساس
     *
     * @var array
     */
    public $order;

    /**
     * انتخاب ها
     *
     * @var array
     */
    public $select;

    /**
     * مقدار های اینسرت/آپدیت
     *
     * @var array
     */
    public $insert;

    /**
     * ستون ها
     *
     * @var QueryCol
     */
    public $queryCol;

    /**
     * ستون
     *
     * @var \Mmb\Db\SingleCol
     */
    public $col;

    /**
     * اسم ستون
     *
     * @var string
     */
    public $colName;

    /**
     * گروه بندی بر اساس
     *
     * @var array
     */
    public $groupBy;

    /**
     * رابطه
     *
     * @var Key\Foreign
     */
    public $foreign_key;


    /**
     * پشتیبانی از انواع تابع ها
     *
     * @var array
     */
    protected $supports = [];

    /**
     * شروع
     *
     * @return void
     */
    public function start($type) {

        if(!in_array($type, $this->supports))
            throw new MmbException(static::class . " not support '$type' query");

        $this->$type();

    }


    /**
     * ورودی ها را بصورت ایمن جایگزاری می کند
     *
     * @param string $query
     * @param mixed ...$args
     * @return string
     */
    public function safeQueryReplace($query, ...$args) {

        return preg_replace_callback('/`\?`|\?/', function($x) use($args){
            static $i = 0;
            if(isset($args[$i])) {
                $arg = $args[$i++];
                if($x[0] == '?') {
                    if ($arg === true)
                        return 1;
                    if ($arg === false)
                        return 0;
                    if ($arg === null)
                        return "NULL";
                    return $this->safeString($arg);
                }
                else{
                    return "`$arg`";
                }
            }
            else{
                return $x[0];
            }
        }, $query);

    }

    public function safeString($string) {

        if($string === false) return 0;
        if($string === true) return 1;
        if($string === null) return 'NULL';

        if(is_int($string) || is_float($string))
            $string = "$string";

        if(!is_string($string))
        {
            throw new TypeException("Query builder given object of '" . typeOf($string) . "', required string");
        }

        return "\"" . addslashes($string) . "\"";

    }

}
