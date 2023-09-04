<?php

namespace Mmb\Db; #auto

/**
 * ارتباط با دیتابیس اصلی
 */
class Db
{

    /**
     * ایجاد یک کوئری
     *
     * @return QueryBuilder
     */
    public static function query()
    {
        return new QueryBuilder;   
    }

    private static ?string $userClass = null;

    /**
     * تنظیم کلاس پیشفرض کاربران
     *
     * @param string $class
     * @return void
     */
    public static function setDefaultUserClass(?string $class)
    {
        static::$userClass = $class;
    }

    /**
     * گرفتن کلاس پیشقرض کاربران
     *
     * @return ?string
     */
    public static function getDefaultUserClass()
    {
        return static::$userClass;
    }

}
