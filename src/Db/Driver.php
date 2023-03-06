<?php

namespace Mmb\Db; #auto

use Mmb\Exceptions\MmbException;
use Mmb\Core\Defaultable;

class Driver {

    use Defaultable;

    /**
     * @var string
     */
    public $queryCompiler;

    /**
     * ایجاد کوئری جدید
     *
     * @return QueryBuilder
     */
    public function query()
    {
        return (new QueryBuilder)->db($this);
    }
    
    /**
     * ارسال کوئری با کامپایلر
     *
     * @param QueryCompiler $queryCompiler
     * @return QueryResult
     */
    public function runQuery($queryCompiler) {

        throw new MmbException("No database driver found");

    }

    /**
     * لود شدن کلاس از کانفیگ
     * 
     * نام های کانفیگ ها با توجه به درایور متفاوت است
     * 
     * @param string $configPrefix
     * @return void
     */
    public function config($configPrefix = 'database')
    {}

    /**
     * لود شدن کلاس از کانفیگ
     * 
     * نام های کانفیگ ها با توجه به درایور متفاوت است
     * 
     * @param string $configPrefix
     * @return void
     */
    public static function configs($configPrefix = 'database')
    {
        static::defaultStatic()->config($configPrefix);
    }

}
