<?php

namespace Mmb\Handlers; #auto

trait Defaultable {

    /**
     * کلاس پیشفرض
     *
     * @var string
     */
    private static $_default;

    /**
     * کلاس مورد نظر را به عنوان کلاس پیشفرض تنظیم می کند
     *
     * @param string $class
     * @return void
     */
    public static function setDefault($class) {

        self::$_default = $class;
        self::$_default_instance = null;

    }

    /**
     * این کلاس را به عنوان کلاس پیشفرض تنظیم می کند
     *
     * @return void
     */
    public static function setAsDefault() {

        self::$_default = static::class;
        self::$_default_instance = null;

    }

    /**
     * تابعی استاتیک را از کلاس پیشفرض صدا می زند
     *
     * @param string $func
     * @param mixed ...$args
     * @return mixed
     */
    public static function callDefaultStatic($func, ...$args) {

        $static = self::$_default;
        return $static::$func(...$args);

    }


    /**
     * ابجکت پیشفرض
     *
     * @var static
     */
    private static $_default_instance = null;

    /**
     * گرفتن شی ای که تنها یکبار ایجاد می شود از کلاس پیشفرض این کلاس
     *
     * @return static
     */
    public static function defaultStatic() {

        $target = self::$_default ?: static::class;

        if(!self::$_default_instance)
            self::$_default_instance = new $target;
        
        return self::$_default_instance;

    }

    /**
     * ساخت شی جدید از کلاس پیشفرض این کلاس
     *
     * @param mixed ...$args
     * @return static
     */
    public static function defaultNew(...$args) {

        $target = self::$_default ?: static::class;

        return new $target(...$args);

    }

}
