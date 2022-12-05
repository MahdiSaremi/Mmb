<?php

namespace Mmb\Db; #auto

/**
 * ارتباط با دیتابیس اصلی
 */
class Db {

    /**
     * ایجاد یک کوئری
     *
     * @return QueryBuilder
     */
    public static function query() {

        return new QueryBuilder;
        
    }

}
